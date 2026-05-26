<?php

use App\Enums\RolesEnum;
use App\Livewire\ProfilePage;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use function Pest\Livewire\livewire;

it('redirects guests to login', function () {
    $this->get('/profile')->assertRedirect('/login');
});

it('renders the profile page for authenticated users', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get('/profile')
        ->assertStatus(200)
        ->assertSeeLivewire(ProfilePage::class);
});

it('pre-populates name and email from the authenticated user', function () {
    $user = User::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);
    actingAs($user);

    livewire(ProfilePage::class)
        ->assertSet('name', 'Jane Doe')
        ->assertSet('email', 'jane@example.com');
});

it('updates profile successfully with valid data', function () {
    $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);
    actingAs($user);

    livewire(ProfilePage::class)
        ->set('name', 'New Name')
        ->set('email', 'new@example.com')
        ->call('updateProfile')
        ->assertHasNoErrors();

    expect($user->fresh()->name)->toBe('New Name')
        ->and($user->fresh()->email)->toBe('new@example.com');
});

it('does not update email when it belongs to another account', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create(['email' => 'mine@example.com']);
    actingAs($user);

    livewire(ProfilePage::class)
        ->set('name', $user->name)
        ->set('email', 'taken@example.com')
        ->call('updateProfile')
        ->assertHasNoErrors();

    expect($user->fresh()->email)->toBe('mine@example.com');
});

it('validates required fields on update profile', function () {
    $user = User::factory()->create();
    actingAs($user);

    livewire(ProfilePage::class)
        ->set('name', '')
        ->set('email', '')
        ->call('updateProfile')
        ->assertHasErrors(['name' => 'required', 'email' => 'required']);
});

it('shows status badge for admin users', function () {
    $adminRole = Role::firstOrCreate(['name' => RolesEnum::ROLE_ADMIN->value]);
    $user = User::factory()->create();
    $user->assignRole($adminRole);

    actingAs($user)
        ->get('/profile')
        ->assertSee(__('profile.account_details.status'));
});

it('does not show status badge for buyer users', function () {
    $buyerRole = Role::firstOrCreate(['name' => RolesEnum::ROLE_BUYER->value]);
    $user = User::factory()->create();
    $user->assignRole($buyerRole);

    actingAs($user)
        ->get('/profile')
        ->assertDontSee(__('profile.account_details.status'));
});

it('changes password successfully with the correct current password', function () {
    $user = User::factory()->create(['password' => bcrypt('oldpassword')]);
    actingAs($user);

    livewire(ProfilePage::class)
        ->set('currentPassword', 'oldpassword')
        ->set('newPassword', 'newpassword123')
        ->set('newPasswordConfirmation', 'newpassword123')
        ->call('changePassword')
        ->assertHasNoErrors()
        ->assertSet('currentPassword', '')
        ->assertSet('newPassword', '')
        ->assertSet('newPasswordConfirmation', '');

    expect(Hash::check('newpassword123', $user->fresh()->password))->toBeTrue();
});

it('does not change password when current password is wrong', function () {
    $user = User::factory()->create(['password' => bcrypt('correctpassword')]);
    $originalHash = $user->password;
    actingAs($user);

    livewire(ProfilePage::class)
        ->set('currentPassword', 'wrongpassword')
        ->set('newPassword', 'newpassword123')
        ->set('newPasswordConfirmation', 'newpassword123')
        ->call('changePassword')
        ->assertHasNoErrors();

    expect($user->fresh()->password)->toBe($originalHash);
});

it('validates that new password and confirmation must match', function () {
    $user = User::factory()->create();
    actingAs($user);

    livewire(ProfilePage::class)
        ->set('currentPassword', 'password')
        ->set('newPassword', 'newpassword123')
        ->set('newPasswordConfirmation', 'differentpassword')
        ->call('changePassword')
        ->assertHasErrors(['newPassword' => 'same']);
});

it('renders past shipping addresses', function () {
    $user  = User::factory()->create();
    $order = makeOrderForUser($user);
    makeAddressForOrder($user, $order);

    actingAs($user)
        ->get('/profile')
        ->assertSee('John Doe')
        ->assertSee('1 Main St');
});

it('shows empty state when user has no addresses', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get('/profile')
        ->assertSee(__('profile.addresses.empty'));
});
