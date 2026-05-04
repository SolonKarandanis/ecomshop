<?php

use App\Livewire\Auth\ForgotPasswordPage;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Support\Facades\Notification;
use function Pest\Livewire\livewire;

it('renders the forgot password page', function () {
    $this->get('/forgot-password')
        ->assertStatus(200)
        ->assertSeeLivewire(ForgotPasswordPage::class);
});

it('validates required email', function () {
    livewire(ForgotPasswordPage::class)
        ->call('submit')
        ->assertHasErrors(['email' => 'required']);
});

it('validates email format', function () {
    livewire(ForgotPasswordPage::class)
        ->set('email', 'not-an-email')
        ->call('submit')
        ->assertHasErrors(['email' => 'email']);
});

it('validates email existence', function () {
    livewire(ForgotPasswordPage::class)
        ->set('email', 'nonexistent@example.com')
        ->call('submit')
        ->assertHasErrors(['email' => 'exists']);
});

it('sends a password reset link successfully', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);

    Notification::fake();

    livewire(ForgotPasswordPage::class)
        ->set('email', 'test@example.com')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSee('Password reset link has been sent to your email.');

    Notification::assertSentTo(
        $user,
        ResetPasswordNotification::class
    );
});
