<?php

use App\Livewire\Auth\RegisterPage;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('renders the register page', function () {
    $this->get('/register')
        ->assertStatus(200)
        ->assertSeeLivewire(RegisterPage::class);
});

it('validates required fields', function () {
    livewire(RegisterPage::class)
        ->call('save')
        ->assertHasErrors([
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
        ]);
});

it('validates name length', function () {
    livewire(RegisterPage::class)
        ->set('name', 'a')
        ->call('save')
        ->assertHasErrors(['name' => 'min']);
});

it('validates email format', function () {
    livewire(RegisterPage::class)
        ->set('email', 'not-an-email')
        ->call('save')
        ->assertHasErrors(['email' => 'email']);
});

it('validates email uniqueness', function () {
    User::factory()->create(['email' => 'test@example.com']);

    livewire(RegisterPage::class)
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->call('save')
        ->assertHasErrors(['email' => 'unique']);
});

it('registers a new user successfully', function () {
    livewire(RegisterPage::class)
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('password', 'password')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    expect(auth()->check())->toBeTrue();
});
