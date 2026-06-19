<?php

use App\Enums\NotificationEventTypeEnum;
use App\Livewire\NotificationsPage;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderNotification;
use function Pest\Livewire\livewire;

it('redirects guests to login', function () {
    $this->get('/notifications')->assertRedirect('/login');
});

it('renders for authenticated buyers', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get('/notifications')
        ->assertStatus(200)
        ->assertSeeLivewire(NotificationsPage::class);
});

it('lists the authenticated users notifications', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    $user->notify(new OrderNotification(
        NotificationEventTypeEnum::ORDER_SHIPPED,
        $order->id,
        "Your order #{$order->id} has been shipped.",
        $user->id,
    ));

    actingAs($user);

    livewire(NotificationsPage::class)
        ->assertSee("Your order #{$order->id} has been shipped.");
});

it('does not show notifications belonging to other buyers', function () {
    $buyer = User::factory()->create();
    $otherBuyer = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $otherBuyer->id]);

    $otherBuyer->notify(new OrderNotification(
        NotificationEventTypeEnum::ORDER_SHIPPED,
        $order->id,
        "Your order #{$order->id} has been shipped.",
        $otherBuyer->id,
    ));

    actingAs($buyer);

    livewire(NotificationsPage::class)
        ->assertDontSee("Your order #{$order->id} has been shipped.");
});
