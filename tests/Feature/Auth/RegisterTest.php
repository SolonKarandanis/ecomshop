<?php

use App\Enums\RolesEnum;
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

it('assigns the Supplier role when registering as Supplier while the Suppliers Feature is enabled', function () {
    config(['features.suppliers_enabled' => true]);

    livewire(RegisterPage::class)
        ->set('name', 'Sam Supplier')
        ->set('email', 'sam@example.com')
        ->set('password', 'password')
        ->set('role', RolesEnum::ROLE_SUPPLIER->value)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $user = User::where('email', 'sam@example.com')->firstOrFail();
    expect($user->hasRole(RolesEnum::ROLE_SUPPLIER->value))->toBeTrue();
    expect($user->hasRole(RolesEnum::ROLE_BUYER->value))->toBeFalse();
});

it('registers a Buyer when Supplier is submitted while the Suppliers Feature is disabled', function () {
    config(['features.suppliers_enabled' => false]);

    livewire(RegisterPage::class)
        ->set('name', 'Sam Tampered')
        ->set('email', 'tampered@example.com')
        ->set('password', 'password')
        ->set('role', RolesEnum::ROLE_SUPPLIER->value)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $user = User::where('email', 'tampered@example.com')->firstOrFail();
    expect($user->hasRole(RolesEnum::ROLE_BUYER->value))->toBeTrue();
    expect($user->hasRole(RolesEnum::ROLE_SUPPLIER->value))->toBeFalse();
});
