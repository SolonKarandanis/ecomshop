# Plan: User Profile Page + User Status

## What changed from the original plan

- **User Status added**: new `UserStatusEnum` (`active` / `inactive`), migration, middleware enforcement, and Filament admin actions.
- **No saved address book**: addresses are read-only, per-order snapshots. No address management.
- **No email re-verification**: `MustVerifyEmail` remains commented out; email updates are immediate.
- **No phone or extra profile fields**: name and email only.
- **Status visible on Profile only for admins** (read-only): inactive users cannot reach the profile page.

---

## Files to Create

| Path | Purpose |
|------|---------|
| `app/Enums/UserStatusEnum.php` | `active` / `inactive` status enum |
| `app/Http/Middleware/CheckUserStatus.php` | Boots inactive users out on every authenticated request |
| `database/migrations/xxxx_add_status_to_users_table.php` | Adds `status` column (default `active`) to `users` |
| `app/Livewire/ProfilePage.php` | Full-page Livewire component |
| `resources/views/livewire/profile-page.blade.php` | Profile page view |
| `app/Dtos/UpdateProfileDto.php` | DTO for profile update |
| `app/Dtos/ChangePasswordDto.php` | DTO for password change |
| `app/Exceptions/ProfileException.php` | Domain exception for profile operations |
| `docs/adr/0001-user-status-enforced-via-middleware.md` | ADR for the middleware enforcement decision |

## Files to Modify

| Path | Change |
|------|--------|
| `routes/web.php` | Add `/profile` route; register `CheckUserStatus` on the `auth` group |
| `app/Models/User.php` | Add `hasMany(Address::class)`; add `status` cast; update `canAccessPanel()` to check status |
| `app/Services/UserService.php` | Add `updateProfile()`, `changePassword()`, `activateUser()`, `deactivateUser()` |
| `app/Repositories/UserRepository.php` | Add `getUserWithAddresses()` |
| `resources/views/livewire/partials/navbar.blade.php` | Wire existing "My Account" placeholder link to `route('profile')` |
| `app/Filament/Resources/Users/Tables/UsersTable.php` | Add `ActivateAction` / `DeactivateAction` row actions (conditionally shown; self-guard) |
| `app/Filament/Resources/Users/Pages/ViewUser.php` | Add same actions as page actions |
| `app/Filament/Resources/Users/Schemas/UserInfolist.php` | Add `status` field |
| `app/Filament/Resources/Users/Schemas/UserForm.php` | Add `status` field |

---

## Implementation

### 1. UserStatusEnum — `app/Enums/UserStatusEnum.php`

Follow `ProductStatusEnum` pattern exactly:

```php
enum UserStatusEnum: string
{
    case Active   = 'user.status.active';
    case Inactive = 'user.status.inactive';

    public static function labels(): array { ... }
    public static function colors(): array
    {
        return [
            'success' => self::Active->value,
            'danger'  => self::Inactive->value,
        ];
    }
}
```

### 2. Migration

```php
$table->string('status')->default(UserStatusEnum::Active->value)->after('password');
```

### 3. User model changes

```php
// Cast
'status' => UserStatusEnum::class,

// Relationship (Address has user_id FK)
public function addresses(): HasMany
{
    return $this->hasMany(Address::class);
}

// Block inactive users from Filament
public function canAccessPanel(Panel $panel): bool
{
    return $this->can('view-admin-panel', User::class)
        && $this->status === UserStatusEnum::Active;
}
```

### 4. Middleware — `app/Http/Middleware/CheckUserStatus.php`

```php
public function handle(Request $request, Closure $next): Response
{
    if (Auth::check() && Auth::user()->status === UserStatusEnum::Inactive) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')
            ->withErrors(['email' => __('auth.inactive')]);
    }
    return $next($request);
}
```

Register it on the `auth` group in `web.php` (or in `bootstrap/app.php` with a group alias).

### 5. Route — `routes/web.php`

```php
Route::middleware(['auth', CheckUserStatus::class])->group(function () {
    Route::get('/profile', ProfilePage::class)->name('profile');
    // ... existing auth routes also get CheckUserStatus
});
```

### 6. DTOs

**`UpdateProfileDto`** — fields: `name`, `email`. Static `fromArray()`.

**`ChangePasswordDto`** — fields: `currentPassword`, `newPassword`, `newPasswordConfirmation`. Static `fromArray()`.

### 7. UserService — new methods

```php
public function updateProfile(User $user, UpdateProfileDto $dto): void
{
    if (User::where('email', $dto->getEmail())->where('id', '!=', $user->id)->exists()) {
        throw new ProfileException(__('messages.update_profile.email_taken'));
    }
    $user->name  = $dto->getName();
    $user->email = $dto->getEmail();
    $this->userRepository->saveUser($user);
}

public function changePassword(User $user, ChangePasswordDto $dto): void
{
    if (!Hash::check($dto->getCurrentPassword(), $user->password)) {
        throw new ProfileException(__('messages.change_password.wrong_current'));
    }
    $user->password = Hash::make($dto->getNewPassword());
    $this->userRepository->saveUser($user);
}

public function activateUser(User $user): void
{
    $user->status = UserStatusEnum::Active;
    $this->userRepository->saveUser($user);
}

public function deactivateUser(User $user): void
{
    $user->status = UserStatusEnum::Inactive;
    $this->userRepository->saveUser($user);
}
```

### 8. UserRepository — new method

```php
public function getUserWithAddresses(int $userId): ?User
{
    return User::with([
        'addresses' => fn ($q) => $q->latest()->limit(5),
    ])->find($userId);
}
```

### 9. ProfilePage — `app/Livewire/ProfilePage.php`

```php
class ProfilePage extends Component
{
    public string $name  = '';
    public string $email = '';

    public string $currentPassword         = '';
    public string $newPassword             = '';
    public string $newPasswordConfirmation = '';

    protected UserService    $userService;
    protected UiService      $uiService;
    protected UserRepository $userRepository;

    public function boot(...): void { ... }

    public function mount(): void
    {
        $this->name  = auth()->user()->name;
        $this->email = auth()->user()->email;
    }

    public function updateProfile(): void { ... }   // validate → DTO → service → showMessage
    public function changePassword(): void { ... }  // validate → DTO → service → showMessage → reset fields

    public function render(): View
    {
        $user = $this->userRepository->getUserWithAddresses(auth()->id());
        return view('livewire.profile-page', compact('user'));
    }
}
```

### 10. Blade view — three sections

**Section 1 — Account Details**: always-visible form, name + email fields, save button.
Status badge shown below the heading only when `auth()->user()->isAdmin()`.

**Section 2 — Change Password**: current password, new password, confirm new password. Save button.

**Section 3 — Past Shipping Addresses** (read-only): loop `$user->addresses` (latest 5). Each row shows full name, street, city, country, postal code, and order label: "Order #123 — 12 Jan 2026".

### 11. Filament — Activate / Deactivate actions

Add to both `UsersTable` (row actions) and `ViewUser` (page actions):

```php
Action::make('deactivate')
    ->requiresConfirmation()
    ->visible(fn (User $record) => $record->status === UserStatusEnum::Active && $record->id !== auth()->id())
    ->action(fn (User $record) => app(UserService::class)->deactivateUser($record)),

Action::make('activate')
    ->visible(fn (User $record) => $record->status === UserStatusEnum::Inactive)
    ->action(fn (User $record) => app(UserService::class)->activateUser($record)),
```

The self-guard (`$record->id !== auth()->id()`) lives on the deactivate action only.

Add `status` as a `TextColumn` (table) and `TextEntry` (infolist) with badge color from `UserStatusEnum::colors()`.

### 12. Navbar

Wire the existing placeholder in `navbar.blade.php`:

```html
<a href="{{ route('profile') }}" wire:navigate ...>
    {{__('navbar.my_account')}}
</a>
```

---

## Verification

```bash
php artisan migrate
composer run test
```

Manual checklist:
1. Register → immediately active, can log in
2. Admin deactivates a user → user's next request logs them out and redirects to login with error
3. Admin cannot deactivate themselves — action is hidden
4. Deactivated admin cannot reach `/admin` panel
5. Profile page: name/email update persists; email conflict shows error
6. Password change: wrong current password shows error; correct change works, old password rejected
7. Past addresses section shows latest 5, each labelled with its order
8. Admins see status badge on their own profile; buyers do not
9. Navbar "My Account" link navigates to `/profile` for authenticated users
