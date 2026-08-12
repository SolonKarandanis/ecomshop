<?php

use App\Enums\OrderStatusEnum;
use App\Enums\RolesEnum;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

function createUserWithRole(RolesEnum $role): User
{
    $roleModel = Role::firstOrCreate(['name' => $role->value]);
    $user = User::factory()->create();
    $user->assignRole($roleModel);

    return $user;
}

function createOrderForOwner(User $owner): Order
{
    $product = Product::factory()->create(['is_active' => true]);
    $order = Order::factory()->create([
        'user_id' => $owner->id,
        'order_status' => OrderStatusEnum::Paid->value,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_amount' => $product->price,
        'total_amount' => $product->price,
    ]);

    Address::create([
        'user_id' => $owner->id,
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

it('denies a buyer viewing another buyer\'s order via OrderDetailsPage', function () {
    $buyerA = createUserWithRole(RolesEnum::ROLE_BUYER);
    $buyerB = createUserWithRole(RolesEnum::ROLE_BUYER);
    $orderForB = createOrderForOwner($buyerB);

    actingAs($buyerA);

    get(route('my-orders.detail', $orderForB->id))->assertForbidden();
});

it('allows a buyer to view their own order via OrderDetailsPage', function () {
    $buyer = createUserWithRole(RolesEnum::ROLE_BUYER);
    $order = createOrderForOwner($buyer);

    actingAs($buyer);

    get(route('my-orders.detail', $order->id))->assertOk();
});

it('allows an admin to view any order via OrderDetailsPage', function () {
    $buyer = createUserWithRole(RolesEnum::ROLE_BUYER);
    $admin = createUserWithRole(RolesEnum::ROLE_ADMIN);
    $order = createOrderForOwner($buyer);

    actingAs($admin);

    get(route('my-orders.detail', $order->id))->assertOk();
});
