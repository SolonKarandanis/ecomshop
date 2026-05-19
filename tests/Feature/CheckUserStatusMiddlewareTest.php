<?php

use App\Enums\UserStatusEnum;
use App\Models\User;

it('allows an active authenticated user to access a protected route', function () {
    $user = User::factory()->create(['status' => UserStatusEnum::ACTIVE->value]);

    actingAs($user)
        ->get(route('my-orders'))
        ->assertStatus(200);
});

it('redirects an inactive user to login when accessing a protected route', function () {
    $user = User::factory()->create(['status' => UserStatusEnum::INACTIVE->value]);

    actingAs($user)
        ->get(route('my-orders'))
        ->assertRedirect(route('login'));
});

it('shows the inactive error message when an inactive user is redirected', function () {
    $user = User::factory()->create(['status' => UserStatusEnum::INACTIVE->value]);

    actingAs($user)
        ->get(route('my-orders'))
        ->assertSessionHasErrors(['email' => __('auth.inactive')]);
});

it('logs out an inactive user who has an existing session', function () {
    $user = User::factory()->create(['status' => UserStatusEnum::INACTIVE->value]);

    actingAs($user)->get(route('my-orders'));

    expect(auth()->check())->toBeFalse();
});

it('does not affect guest requests', function () {
    $this->get(route('my-orders'))
        ->assertRedirect(route('login'));

    expect(auth()->check())->toBeFalse();
});
