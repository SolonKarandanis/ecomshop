<?php

use App\Enums\NotificationEventTypeEnum;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderNotification;
use App\Repositories\NotificationRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

it('retrieves paginated user notifications correctly', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    // Send 3 notifications to the user with distinct timestamps
    for ($i = 1; $i <= 3; $i++) {
        Carbon::setTestNow(Carbon::now()->addSeconds(1));
        $user->notify(new OrderNotification(
            NotificationEventTypeEnum::ORDER_SHIPPED,
            $order->id,
            "Notification #{$i}",
            $user->id,
        ));
    }
    Carbon::setTestNow(); // Reset mocked time

    // Create another user and send a notification to them
    $otherUser = User::factory()->create();
    $otherUser->notify(new OrderNotification(
        NotificationEventTypeEnum::ORDER_SHIPPED,
        $order->id,
        "Other Notification",
        $otherUser->id,
    ));

    $repository = new NotificationRepository();
    $results = $repository->getUsersNotifications($user->id);

    expect($results)->toBeInstanceOf(LengthAwarePaginator::class);
    expect($results->total())->toBe(3);
    expect($results->items()[0]->data['message'])->toBe('Notification #3'); // Ordered by latest first (Notification #3 was created last)
});
