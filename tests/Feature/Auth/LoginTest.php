<?php

use App\Livewire\Auth\LoginPage;
use App\Models\User;
use function Pest\Livewire\livewire;

it('renders the login page', function () {
    $this->get('/login')
        ->assertStatus(200)
        ->assertSeeLivewire(LoginPage::class);
});

it('validates required fields', function () {
    livewire(LoginPage::class)
        ->call('performLogin')
        ->assertHasErrors([
            'email' => 'required',
            'password' => 'required',
        ]);
});

it('validates email format', function () {
    livewire(LoginPage::class)
        ->set('email', 'not-an-email')
        ->set('password', 'password')
        ->call('performLogin')
        ->assertHasErrors(['email' => 'email']);
});

it('fails login with invalid credentials', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    livewire(LoginPage::class)
        ->set('email', 'test@example.com')
        ->set('password', 'wrong-password')
        ->call('performLogin')
        ->assertSee('Invalid Credentials');

    expect(auth()->check())->toBeFalse();
});

it('logs in successfully with valid credentials', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    livewire(LoginPage::class)
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->call('performLogin')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect(auth()->check())->toBeTrue();
});
