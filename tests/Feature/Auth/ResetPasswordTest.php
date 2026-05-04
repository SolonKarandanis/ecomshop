<?php

use App\Livewire\Auth\ResetPasswordPage;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use function Pest\Livewire\livewire;

it('renders the reset password page', function () {
    $this->get('/reset-password/valid-token?email=test@example.com')
        ->assertStatus(200)
        ->assertSeeLivewire(ResetPasswordPage::class);
});

it('validates required fields', function () {
    livewire(ResetPasswordPage::class, ['token' => ''])
        ->call('submit')
        ->assertHasErrors([
            'email' => 'required',
            'token' => 'required',
            'password' => 'required',
        ]);
});

it('validates email format', function () {
    livewire(ResetPasswordPage::class, ['token' => 'some-token'])
        ->set('email', 'not-an-email')
        ->call('submit')
        ->assertHasErrors(['email' => 'email']);
});

it('validates password confirmation', function () {
    livewire(ResetPasswordPage::class, ['token' => 'some-token'])
        ->set('email', 'test@example.com')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'different-password')
        ->call('submit')
        ->assertHasErrors(['password' => 'confirmed']);
});

it('validates password length', function () {
    livewire(ResetPasswordPage::class, ['token' => 'some-token'])
        ->set('email', 'test@example.com')
        ->set('password', 'short')
        ->set('password_confirmation', 'short')
        ->call('submit')
        ->assertHasErrors(['password' => 'min']);
});

it('resets password successfully', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('old-password'),
    ]);

    $token = Password::createToken($user);

    livewire(ResetPasswordPage::class, ['token' => $token])
        ->set('email', 'test@example.com')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertRedirect(route('login'));

    $user->refresh();
    expect(Hash::check('new-password', $user->password))->toBeTrue();
});

it('shows error message with invalid token', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('old-password'),
    ]);

    livewire(ResetPasswordPage::class, ['token' => 'invalid-token'])
        ->set('email', 'test@example.com')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('submit')
        ->assertSee('Something went wrong, please try again');

    $user->refresh();
    expect(Hash::check('old-password', $user->password))->toBeTrue();
});
