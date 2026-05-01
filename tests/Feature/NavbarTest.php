<?php

use App\Models\User;

it('shows common navbar elements', function () {
    $this->get('/')
        ->assertSee(__('navbar.home'))
        ->assertSee(__('navbar.categories'))
        ->assertSee(__('navbar.products'));
});

it('shows login button for guests', function () {
    $this->get('/')
        ->assertSee(__('navbar.buttons.log_in'))
        ->assertDontSee(__('navbar.logout'));
});

it('shows user name and logout for authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/')
        ->assertSee($user->name)
        ->assertSee(__('navbar.logout'))
        ->assertDontSee(__('navbar.buttons.log_in'));
});
