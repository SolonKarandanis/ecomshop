<?php

use App\Enums\NotificationEventTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\RolesEnum;
use App\Livewire\OrderDetailsPage;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderNotification;
use App\Services\UiService;
use Illuminate\Support\Facades\Notification;
use Mockery\MockInterface;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    config(['features.suppliers_enabled' => true]);
    $this->mock(UiService::class, function (MockInterface $mock) {
        $mock->shouldReceive('showMessage')->andReturn();
    });
});

function createSupplier(): User
{
    $role = Role::firstOrCreate(['name' => RolesEnum::ROLE_SUPPLIER->value]);
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function createOrderForSupplier(User $supplier, string $orderStatus): Order
{
    $buyer = User::factory()->create();
    $product = Product::factory()->create(['is_active' => true, 'supplier_id' => $supplier->id]);
    $order = Order::factory()->create([
        'user_id' => $buyer->id,
        'order_status' => $orderStatus,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_amount' => $product->price,
        'total_amount' => $product->price,
    ]);

    Address::create([
        'user_id' => $buyer->id,
        'order_id' => $order->id,
        'first_name' => 'Test',
        'last_name' => 'Buyer',
        'phone' => '5555555555',
        'street_address' => '123 Main St',
        'city' => 'Athens',
        'country' => 'GR',
        'postal_code' => '12345',
    ]);

    return $order;
}

it('allows a supplier to mark a paid order containing their product as shipped, and dispatches the notification', function () {
    Notification::fake();
    $supplier = createSupplier();
    $order = createOrderForSupplier($supplier, OrderStatusEnum::Paid->value);
    actingAs($supplier);

    livewire(OrderDetailsPage::class, ['id' => $order->id])
        ->call('markAsShipped');

    expect($order->refresh()->order_status)->toBe(OrderStatusEnum::Shipped->value);
    Notification::assertSentTo($order->user, OrderNotification::class, function ($notification) use ($order) {
        return $notification->toDatabase($order->user)['event_type'] === NotificationEventTypeEnum::ORDER_SHIPPED->value;
    });
});

it('allows a supplier to cancel a paid order containing their product, and dispatches the notification', function () {
    Notification::fake();
    $supplier = createSupplier();
    $order = createOrderForSupplier($supplier, OrderStatusEnum::Paid->value);
    actingAs($supplier);

    livewire(OrderDetailsPage::class, ['id' => $order->id])
        ->call('cancelOrder');

    expect($order->refresh()->order_status)->toBe(OrderStatusEnum::Cancelled->value);
    Notification::assertSentTo($order->user, OrderNotification::class, function ($notification) use ($order) {
        return $notification->toDatabase($order->user)['event_type'] === NotificationEventTypeEnum::ORDER_CANCELLED->value;
    });
});

it('allows a supplier to mark a shipped order containing their product as delivered, and dispatches the notification', function () {
    Notification::fake();
    $supplier = createSupplier();
    $order = createOrderForSupplier($supplier, OrderStatusEnum::Shipped->value);
    actingAs($supplier);

    livewire(OrderDetailsPage::class, ['id' => $order->id])
        ->call('markAsDelivered');

    expect($order->refresh()->order_status)->toBe(OrderStatusEnum::Delivered->value);
    Notification::assertSentTo($order->user, OrderNotification::class, function ($notification) use ($order) {
        return $notification->toDatabase($order->user)['event_type'] === NotificationEventTypeEnum::ORDER_DELIVERED->value;
    });
});

it('prevents a supplier from acting on another supplier\'s order', function () {
    $supplierA = createSupplier();
    $supplierB = createSupplier();
    $orderForB = createOrderForSupplier($supplierB, OrderStatusEnum::Paid->value);
    actingAs($supplierA);

    // The order doesn't contain any of supplierA's products, so canViewOrder already
    // blocks access to the page before the supplier-action gate is even reached.
    get(route('my-orders.detail', $orderForB->id))->assertForbidden();

    expect($orderForB->refresh()->order_status)->toBe(OrderStatusEnum::Paid->value);
});

it('prevents a supplier from viewing an order that does not contain their product', function () {
    $supplierA = createSupplier();
    $supplierB = createSupplier();
    $orderForB = createOrderForSupplier($supplierB, OrderStatusEnum::Paid->value);
    actingAs($supplierA);

    get(route('my-orders.detail', $orderForB->id))->assertForbidden();
});

it('allows a supplier to view an order containing their product even though they did not place it', function () {
    $supplier = createSupplier();
    $order = createOrderForSupplier($supplier, OrderStatusEnum::Paid->value);
    actingAs($supplier);

    get(route('my-orders.detail', $order->id))->assertOk();
});

it('blocks a supplier action when the suppliers feature flag is disabled', function () {
    $supplier = createSupplier();
    $order = createOrderForSupplier($supplier, OrderStatusEnum::Paid->value);
    config(['features.suppliers_enabled' => false]);
    actingAs($supplier);

    livewire(OrderDetailsPage::class, ['id' => $order->id])
        ->call('markAsShipped');

    expect($order->refresh()->order_status)->toBe(OrderStatusEnum::Paid->value);
});

it('does not allow a supplier to transition an order out of a terminal status', function () {
    $supplier = createSupplier();
    $order = createOrderForSupplier($supplier, OrderStatusEnum::Delivered->value);
    actingAs($supplier);

    livewire(OrderDetailsPage::class, ['id' => $order->id])
        ->call('markAsShipped');

    expect($order->refresh()->order_status)->toBe(OrderStatusEnum::Delivered->value);
});
