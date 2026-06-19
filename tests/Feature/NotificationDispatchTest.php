<?php

use App\Enums\NotificationEventTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
});

it('dispatches ORDER_CREATED notification to the buyer', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    $user->notify(new OrderNotification(
        NotificationEventTypeEnum::ORDER_CREATED,
        $order->id,
        "Your order #{$order->id} has been placed successfully.",
        $user->id,
    ));

    Notification::assertSentTo($user, OrderNotification::class, function ($notification) {
        $payload = $notification->toDatabase(new stdClass);
        return $payload['event_type'] === NotificationEventTypeEnum::ORDER_CREATED->value;
    });
});

it('dispatches ORDER_PAYMENT_CONFIRMED with correct event type', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    $user->notify(new OrderNotification(
        NotificationEventTypeEnum::ORDER_PAYMENT_CONFIRMED,
        $order->id,
        "Payment for order #{$order->id} has been confirmed.",
        $user->id,
    ));

    Notification::assertSentTo($user, OrderNotification::class, function ($notification) {
        $payload = $notification->toDatabase(new stdClass);
        return $payload['event_type'] === NotificationEventTypeEnum::ORDER_PAYMENT_CONFIRMED->value;
    });
});

it('dispatches ORDER_PAYMENT_FAILED with correct event type', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    $user->notify(new OrderNotification(
        NotificationEventTypeEnum::ORDER_PAYMENT_FAILED,
        $order->id,
        "Payment for order #{$order->id} failed.",
        $user->id,
    ));

    Notification::assertSentTo($user, OrderNotification::class, function ($notification) {
        $payload = $notification->toDatabase(new stdClass);
        return $payload['event_type'] === NotificationEventTypeEnum::ORDER_PAYMENT_FAILED->value;
    });
});

it('dispatches ORDER_SHIPPED when order_status changes to Shipped', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id'      => $user->id,
        'order_status' => OrderStatusEnum::Paid->value,
    ]);

    $order->update(['order_status' => OrderStatusEnum::Shipped->value]);

    Notification::assertSentTo($user, OrderNotification::class, function ($notification) {
        $payload = $notification->toDatabase(new stdClass);
        return $payload['event_type'] === NotificationEventTypeEnum::ORDER_SHIPPED->value;
    });
});

it('dispatches ORDER_DELIVERED when order_status changes to Delivered', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id'      => $user->id,
        'order_status' => OrderStatusEnum::Shipped->value,
    ]);

    $order->update(['order_status' => OrderStatusEnum::Delivered->value]);

    Notification::assertSentTo($user, OrderNotification::class, function ($notification) {
        $payload = $notification->toDatabase(new stdClass);
        return $payload['event_type'] === NotificationEventTypeEnum::ORDER_DELIVERED->value;
    });
});

it('dispatches ORDER_CANCELLED when order_status changes to Cancelled', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id'      => $user->id,
        'order_status' => OrderStatusEnum::Paid->value,
    ]);

    $order->update(['order_status' => OrderStatusEnum::Cancelled->value]);

    Notification::assertSentTo($user, OrderNotification::class, function ($notification) {
        $payload = $notification->toDatabase(new stdClass);
        return $payload['event_type'] === NotificationEventTypeEnum::ORDER_CANCELLED->value;
    });
});

it('does not dispatch a notification when order_status transitions to Paid', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id'      => $user->id,
        'order_status' => OrderStatusEnum::Draft->value,
    ]);

    $order->update(['order_status' => OrderStatusEnum::Paid->value]);

    Notification::assertNothingSent();
});

it('does not dispatch a notification when non-status fields change', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    $order->update(['notes' => 'Leave at door']);

    Notification::assertNothingSent();
});

it('notification payload contains all required fields', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    $notification = new OrderNotification(
        NotificationEventTypeEnum::ORDER_CREATED,
        $order->id,
        'Test message',
        $user->id,
    );

    $payload = $notification->toDatabase($user);

    expect($payload)
        ->toHaveKeys(['event_type', 'order_id', 'order_url', 'message'])
        ->and($payload['event_type'])->toBe(NotificationEventTypeEnum::ORDER_CREATED->value)
        ->and($payload['order_id'])->toBe($order->id)
        ->and($payload['message'])->toBe('Test message');
});
