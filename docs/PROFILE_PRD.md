# PRD: User Profile Page + User Status

## Problem Statement

Authenticated users have no way to view or update their own account details — there is no profile page, no way to change their name, email, or password from the storefront, and no single place to review their past shipping addresses. Separately, admins have no way to suspend a user account; once registered, a user can always log in regardless of any administrative action needed.

## Solution

Add a Profile page to the storefront where authenticated users can update their name, email, and password, and view their past shipping addresses (read-only). Introduce a User Status (active / inactive) controlled exclusively by admins via the Filament admin panel. Inactive users are blocked from logging in and any existing session is terminated on their next request.

## User Stories

1. As an authenticated user, I want to view my current name and email on a profile page, so that I can confirm my account details are correct.
2. As an authenticated user, I want to update my name on the profile page, so that my display name reflects who I am.
3. As an authenticated user, I want to update my email address on the profile page, so that I receive communications at my current address.
4. As an authenticated user, I want to be shown an error when I try to set my email to one already used by another account, so that I understand why the update was rejected.
5. As an authenticated user, I want to change my password from the profile page, so that I can keep my account secure without going through a password reset flow.
6. As an authenticated user, I want to be shown an error when I enter the wrong current password during a password change, so that I know the change did not take effect.
7. As an authenticated user, I want my new password confirmation to be validated before saving, so that I do not accidentally set a password I did not intend.
8. As an authenticated user, I want to see my past shipping addresses on my profile page, so that I can review where my previous orders were delivered.
9. As an authenticated user, I want each past address to be labelled with its order number and date, so that I can match an address to a specific purchase.
10. As an authenticated user, I want to reach my profile page from the navigation bar, so that I can access it from any page.
11. As an admin user, I want to see my own User Status on my profile page, so that I can confirm my account is active.
12. As an admin, I want to deactivate a user account from the users list in the admin panel, so that I can suspend access quickly without leaving the list view.
13. As an admin, I want to deactivate a user account from the user detail page in the admin panel, so that I can take action while reviewing a specific user's information.
14. As an admin, I want to activate a previously inactive user from the users list, so that I can restore access without navigating away.
15. As an admin, I want to activate a previously inactive user from the user detail page, so that I can restore access while reviewing their profile.
16. As an admin, I want the deactivate action to be hidden for my own account, so that I cannot accidentally lock myself out of the system.
17. As an admin, I want to see a user's status (active / inactive) in the users list and on the user detail page, so that I can assess account standing at a glance.
18. As an inactive user, I want to be shown a clear error message when I try to log in, so that I understand why my credentials were rejected.
19. As an inactive user who is already logged in when deactivated, I want my session to be terminated on my next request, so that deactivation takes immediate effect.
20. As a newly registered user, I want my account to be active by default, so that I can start shopping immediately after registration.

## Implementation Decisions

### User Status

- A new `UserStatusEnum` with two cases — `active` and `inactive` — stored as strings in the database following the project's existing dot-notation convention (e.g. `user.status.active`).
- A new `status` column is added to the `users` table via migration, defaulting to `active`.
- The `User` model gains a `status` enum cast and an `addresses` has-many relationship (the `addresses` table already has a `user_id` foreign key).

### Status Enforcement

- A dedicated middleware checks `Auth::user()->status` on every authenticated storefront request. If the user is inactive, the middleware logs them out, invalidates the session, regenerates the CSRF token, and redirects to the login page with an error.
- The middleware is registered on the existing `auth` route group in `web.php`, covering all authenticated storefront routes including the new profile route.
- Filament admin panel access is blocked separately by extending `canAccessPanel()` on the `User` model to also require `status === active`. Filament uses its own middleware stack, so the web middleware does not cover it.
- The self-guard for the deactivate action (`$record->id !== auth()->id()`) lives on the action itself — the action is hidden, not disabled, when the admin is viewing their own record.

### Profile Page

- A full-page Livewire component at `/profile`, behind the `auth` middleware group.
- Two independent forms on a single page: Account Details (name + email) and Change Password (current, new, confirm). Submitting one form does not affect the other.
- Status is displayed as a read-only badge below the Account Details heading, rendered only when the authenticated user is an admin.
- Past shipping addresses are loaded from the database (latest 5, ordered by creation date descending, not deduplicated — each row is tied to a specific order). Each row displays full name, street, city, country, postal code, and an order label ("Order #ID — DD Mon YYYY").
- The existing "My Account" placeholder link in the navbar (which already exists with no `href`) is wired to `route('profile')`.

### Business Logic (UserService)

- `updateProfile(User, UpdateProfileDto)`: checks email uniqueness against other users, then saves. Throws `ProfileException` on conflict.
- `changePassword(User, ChangePasswordDto)`: verifies the current password with `Hash::check`, then saves the hashed new password. Throws `ProfileException` on mismatch.
- `activateUser(User)`: sets status to `active` and saves.
- `deactivateUser(User)`: sets status to `inactive` and saves.
- All four methods are added to the existing `UserService`, which is already registered as a singleton in `AppServiceProvider`.

### Data Access (UserRepository)

- One new method: `getUserWithAddresses(int $userId)` — eager-loads the user's latest 5 addresses in a single query.

### Filament Admin Panel

- Activate and Deactivate are two separate actions (not a toggle), conditionally visible based on current status.
- Both actions appear in two places: as row actions in the users table and as page actions on the View User page.
- A `status` column is added to the users table view (badge with colour from `UserStatusEnum::colors()`).
- A `status` entry is added to the User infolist and the User form.

### DTOs and Exceptions

- `UpdateProfileDto`: fields `name`, `email`. Static `fromArray()`, following the existing DTO pattern.
- `ChangePasswordDto`: fields `currentPassword`, `newPassword`, `newPasswordConfirmation`. Static `fromArray()`.
- `ProfileException`: empty extension of `\Exception`, thrown by `UserService` for profile-related domain errors.

## Testing Decisions

A good test exercises observable behaviour through the module's public interface — what the user sees, what gets persisted, what gets dispatched — not internal implementation details like which repository method was called.

### UserService

Unit/feature tests covering:
- `updateProfile` saves name and email when valid.
- `updateProfile` throws `ProfileException` when the new email belongs to another user.
- `changePassword` saves the new hashed password when the current password is correct.
- `changePassword` throws `ProfileException` when the current password is wrong.
- `activateUser` sets status to `active`.
- `deactivateUser` sets status to `inactive`.

Prior art: plain PHPUnit in `tests/Unit/`, `RefreshDatabase` where models are needed.

### CheckUserStatus Middleware

Feature tests covering:
- An active user can access an authenticated route.
- An inactive user is redirected to login on an authenticated route.
- An inactive user who is mid-session (already authenticated) is logged out and redirected on their next request.
- A guest is not affected by the middleware.

Prior art: `tests/Feature/Auth/LoginTest.php` (authentication flow pattern).

### ProfilePage Livewire Component

Livewire feature tests covering:
- Page renders with name and email pre-populated from the authenticated user.
- Calling `updateProfile` with valid data updates the database record.
- Calling `updateProfile` with a duplicate email shows a validation/domain error.
- Calling `changePassword` with the correct current password updates the password.
- Calling `changePassword` with an incorrect current password shows an error.
- Past addresses are rendered on the page.
- Status badge is visible when the authenticated user is an admin.
- Status badge is not visible when the authenticated user is a buyer.

Prior art: `tests/Feature/ProductDetailPageTest.php` (Livewire + `UiService` mock pattern), `tests/Feature/Auth/LoginTest.php`.

## Out of Scope

- **Saved address book**: addresses remain per-order snapshots. There is no feature to save, edit, or reuse addresses independently of an order.
- **Email re-verification**: changing email takes effect immediately. `MustVerifyEmail` remains unused.
- **Additional profile fields**: no phone number, avatar, or other user attributes beyond name and email.
- **User self-service deactivation**: users cannot deactivate their own accounts.
- **Role management from the profile page**: role assignment remains admin-only via the Filament panel.
- **Supplier or admin-specific profile sections**: all authenticated user roles see the same profile page layout (admins additionally see their status badge).

## Further Notes

- The "My Account" nav link already exists in the navbar blade with `{{__('navbar.my_account')}}` and no `href` — it was clearly placeholder for this feature. Wiring it up requires only adding `href="{{ route('profile') }}"` and `wire:navigate`.
- The `Address` model already has a `user_id` foreign key and a `belongsTo(User::class)` relationship. The inverse `hasMany(Address::class)` on `User` is the only model change needed to support the addresses section.
- An ADR has been recorded at `docs/adr/0001-user-status-enforced-via-middleware.md` documenting the choice of middleware over a LoginPage-only check.
