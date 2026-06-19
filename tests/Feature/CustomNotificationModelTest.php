<?php

use App\Enums\NotificationEventTypeEnum;
use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderNotification;

it('can query notifications directly using the custom Notification model', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    $user->notify(new OrderNotification(
        NotificationEventTypeEnum::ORDER_SHIPPED,
        $order->id,
        "Your order #{$order->id} has been shipped.",
        $user->id,
    ));

    // Verify direct retrieval
    $notifications = Notification::all();
    expect($notifications)->toHaveCount(1);

    $notification = $notifications->first();
    expect($notification->notifiable)->toBeInstanceOf(User::class);
    expect($notification->notifiable->id)->toBe($user->id);
    expect($notification->data['message'])->toContain("has been shipped");
});

it('supports custom query scopes on the custom Notification model', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    // Send first notification (unread)
    $user->notify(new OrderNotification(
        NotificationEventTypeEnum::ORDER_SHIPPED,
        $order->id,
        "Unread notification",
        $user->id,
    ));

    // Send second notification and mark as read
    $user->notify(new OrderNotification(
        NotificationEventTypeEnum::ORDER_SHIPPED,
        $order->id,
        "Read notification",
        $user->id,
    ));

    $readNotification = Notification::where('data->message', 'Read notification')->first();
    $readNotification->markAsRead();

    // Verify unread scope
    $unreadNotifications = Notification::onlyUnread()->get();
    expect($unreadNotifications)->toHaveCount(1);
    expect($unreadNotifications->first()->data['message'])->toBe('Unread notification');

    // Verify read scope
    $readNotifications = Notification::onlyRead()->get();
    expect($readNotifications)->toHaveCount(1);
    expect($readNotifications->first()->data['message'])->toBe('Read notification');

    // Verify ofType scope
    $orderNotifications = Notification::ofType(OrderNotification::class)->get();
    expect($orderNotifications)->toHaveCount(2);
});

it('supports filtering by user via the forUser scope', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user1->id]);

    $user1->notify(new OrderNotification(
        NotificationEventTypeEnum::ORDER_SHIPPED,
        $order->id,
        "Notification for user 1",
        $user1->id,
    ));

    $user2->notify(new OrderNotification(
        NotificationEventTypeEnum::ORDER_SHIPPED,
        $order->id,
        "Notification for user 2",
        $user2->id,
    ));

    // Verify filtering using User model instance
    $user1Notifications = Notification::forUser($user1)->get();
    expect($user1Notifications)->toHaveCount(1);
    expect($user1Notifications->first()->data['message'])->toBe('Notification for user 1');

    // Verify filtering using direct user ID
    $user2Notifications = Notification::forUser($user2->id)->get();
    expect($user2Notifications)->toHaveCount(1);
    expect($user2Notifications->first()->data['message'])->toBe('Notification for user 2');
});
