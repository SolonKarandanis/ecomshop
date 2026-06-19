<?php

use App\Enums\NotificationEventTypeEnum;
use App\Livewire\Partials\Navbar;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderNotification;
use function Pest\Livewire\livewire;

it('bell icon is absent for guests', function () {
    $this->get('/')->assertDontSee('aria-label="Notifications"', false);
});

it('bell badge shows correct unread count for authenticated buyer', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    $user->notify(new OrderNotification(
        NotificationEventTypeEnum::ORDER_CREATED,
        $order->id,
        "Your order #{$order->id} has been placed.",
        $user->id,
    ));

    actingAs($user);

    livewire(Navbar::class)
        ->assertSet('unread_notifications_count', 1);
});

it('opening the dropdown marks notifications as read and count drops to zero', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    $user->notify(new OrderNotification(
        NotificationEventTypeEnum::ORDER_CREATED,
        $order->id,
        "Your order #{$order->id} has been placed.",
        $user->id,
    ));

    actingAs($user);

    livewire(Navbar::class)
        ->assertSet('unread_notifications_count', 1)
        ->call('openDropdown')
        ->assertSet('unread_notifications_count', 0);

    expect($user->unreadNotifications()->count())->toBe(0);
});

it('notificationReceived event refreshes unread count', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    actingAs($user);

    $component = livewire(Navbar::class)
        ->assertSet('unread_notifications_count', 0);

    $user->notify(new OrderNotification(
        NotificationEventTypeEnum::ORDER_CREATED,
        $order->id,
        "Your order #{$order->id} has been placed.",
        $user->id,
    ));

    $component->dispatch('notificationReceived')
        ->assertSet('unread_notifications_count', 1);
});
