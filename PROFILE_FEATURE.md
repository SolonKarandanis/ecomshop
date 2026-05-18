# Plan: User Profile Page

## Context

Authenticated users currently have no way to view or edit their account details. There is no `/profile` route, no name/email/password update flow, and no single place to see past shipping addresses. This adds a standard account page that sits alongside the existing `MyOrdersPage` in the authenticated section of the storefront.

No new database tables are needed — this feature uses the existing `users` and `addresses` tables.

---

## Files to Create

| Path | Purpose |
|------|---------|
| `app/Livewire/ProfilePage.php` | Full-page Livewire component |
| `resources/views/livewire/profile-page.blade.php` | Profile page view |
| `app/Dtos/UpdateProfileDto.php` | DTO for profile update |
| `app/Dtos/ChangePasswordDto.php` | DTO for password change |
| `app/Exceptions/ProfileException.php` | Domain exception |

## Files to Modify

| Path | Change |
|------|--------|
| `routes/web.php` | Add `/profile` route under auth middleware |
| `app/Services/UserService.php` | Add `updateProfile()` and `changePassword()` methods |
| `app/Repositories/UserRepository.php` | Add `getUserWithAddresses()` method |
| `app/Livewire/Partials/Navbar.php` (or its view) | Add Profile link to nav for authenticated users |

---

## Implementation Steps

### 1. Route — `routes/web.php`

Add inside the existing `auth` middleware group alongside the orders routes:

```php
Route::get('/profile', ProfilePage::class)->name('profile');
```

### 2. DTOs

**`app/Dtos/UpdateProfileDto.php`** — fields: `name`, `email`. Static `fromArray()`. Getters/setters matching `CheckoutDTO` style.

**`app/Dtos/ChangePasswordDto.php`** — fields: `currentPassword`, `newPassword`, `newPasswordConfirmation`. Static `fromArray()`. Getters/setters.

### 3. Exception — `app/Exceptions/ProfileException.php`

```php
class ProfileException extends \Exception {}
```

Thrown by `UserService` when password verification fails.

### 4. UserRepository — add one method

```php
public function getUserWithAddresses(int $userId): ?User
{
    return User::with([
        'addresses' => fn ($q) => $q->latest()->limit(5)
    ])->find($userId);
}
```

Note: `addresses` belongs to `Order` not directly to `User`, but the `addresses` table has a `user_id` FK column. Add `hasMany(Address::class)` to the `User` model if not already present.

### 5. UserService — add two methods

```php
/**
 * @throws ProfileException if email is already taken by another user
 */
public function updateProfile(User $user, UpdateProfileDto $dto): void
{
    if (User::where('email', $dto->getEmail())->where('id', '!=', $user->id)->exists()) {
        throw new ProfileException('This email is already in use.');
    }
    $user->name  = $dto->getName();
    $user->email = $dto->getEmail();
    $this->userRepository->saveUser($user);
}

/**
 * @throws ProfileException if current password is wrong
 */
public function changePassword(User $user, ChangePasswordDto $dto): void
{
    if (!Hash::check($dto->getCurrentPassword(), $user->password)) {
        throw new ProfileException('Current password is incorrect.');
    }
    $user->password = Hash::make($dto->getNewPassword());
    $this->userRepository->saveUser($user);
}
```

No singleton re-registration needed — `UserService` is already registered in `AppServiceProvider`.

### 6. ProfilePage Livewire component — `app/Livewire/ProfilePage.php`

```php
class ProfilePage extends Component
{
    // Profile form
    public string $name  = '';
    public string $email = '';

    // Password form
    public string $currentPassword          = '';
    public string $newPassword              = '';
    public string $newPasswordConfirmation  = '';

    protected UserService $userService;
    protected UiService   $uiService;

    public function boot(UserService $userService, UiService $uiService): void { ... }

    public function mount(): void
    {
        $user        = auth()->user();
        $this->name  = $user->name;
        $this->email = $user->email;
    }

    public function updateProfile(): void
    {
        $this->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ]);
        // build UpdateProfileDto, call UserService::updateProfile()
        // catch ProfileException → error toast
        // on success → success toast
    }

    public function changePassword(): void
    {
        $this->validate([
            'currentPassword'         => ['required'],
            'newPassword'             => ['required', 'min:8', 'confirmed'],
            'newPasswordConfirmation' => ['required'],
        ]);
        // build ChangePasswordDto, call UserService::changePassword()
        // catch ProfileException → error toast
        // on success → reset password fields, success toast
    }

    public function render(): View
    {
        $user = $this->userRepository->getUserWithAddresses(auth()->id());
        return view('livewire.profile-page', compact('user'));
    }
}
```

Use `$this->uiService->showMessage()` for all feedback (same as `ProductDetailPage`/`WithCartActions`).

### 7. Blade view — `resources/views/livewire/profile-page.blade.php`

Two-section layout matching the style of `my-orders-page.blade.php`:

**Section 1 — Account Details**
- Display current name and email as read-only text
- "Edit" button reveals an inline form (`wire:model` on name + email, `wire:click="updateProfile"` on save)
- Or always-visible form fields — simpler, matches register page style

**Section 2 — Change Password**
- Three password inputs: Current Password, New Password, Confirm New Password
- `wire:model.defer` on all three, `wire:click="changePassword"` on submit button

**Section 3 — Past Shipping Addresses** (read-only)
- Loop `$user->addresses` (latest 5, from orders)
- Show: full name, street, city, country, postal code
- Label each with the order date: "Used on order #123 — 12 Jan 2026"

**Navbar link**: Add a "My Profile" link in the authenticated user dropdown in `Partials/Navbar` view, alongside the existing "My Orders" link.

---

## Verification

```bash
# No new migration needed — run existing tests to confirm nothing is broken
composer run test
```

Manual verification:
1. Log in, navigate to `/profile` — name and email pre-populated
2. Change name/email, save — success toast, values updated in DB
3. Try to set email to one already used by another user — error toast
4. Change password with wrong current password — error toast
5. Change password correctly — success toast, can log in with new password
6. Past addresses section shows addresses from previous orders
7. Navbar shows "My Profile" link for authenticated users
