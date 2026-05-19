<?php

use App\Enums\RolesEnum;
use App\Enums\UserStatusEnum;
use App\Models\Address;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\UserService;
use App\Dtos\CreateUserDTO;
use Spatie\Permission\Models\Role;

function makeOrderForUser(User $user): Order
{
    $paymentMethod = PaymentMethod::create(['resource_key' => 'cash']);
    return Order::create(['user_id' => $user->id, 'payment_method_id' => $paymentMethod->id]);
}

function makeAddressForOrder(User $user, Order $order): Address
{
    return Address::create([
        'user_id'        => $user->id,
        'order_id'       => $order->id,
        'first_name'     => 'John',
        'last_name'      => 'Doe',
        'phone'          => '555-0100',
        'street_address' => '1 Main St',
        'city'           => 'Athens',
        'country'        => 'GR',
        'postal_code'    => '10001',
    ]);
}

it('casts status to UserStatusEnum', function () {
    $user = User::factory()->create();

    expect($user->status)->toBeInstanceOf(UserStatusEnum::class);
});

it('defaults new users to active status', function () {
    $user = User::factory()->create();

    expect($user->status)->toBe(UserStatusEnum::ACTIVE);
});

it('createBuyer produces an active user', function () {
    Role::firstOrCreate(['name' => RolesEnum::ROLE_BUYER->value]);

    $dto = CreateUserDTO::fromArray([
        'name' => 'Test User',
        'email' => 'buyer@example.com',
        'password' => 'password',
    ]);
    $user = app(UserService::class)->createBuyer($dto);

    expect($user->fresh()->status)->toBe(UserStatusEnum::ACTIVE);
});

it('user addresses relationship returns addresses belonging to the user', function () {
    $user  = User::factory()->create();
    $order = makeOrderForUser($user);
    makeAddressForOrder($user, $order);

    expect($user->addresses)->toHaveCount(1);
    expect($user->addresses->first()->user_id)->toBe($user->id);
});

it('user addresses relationship returns only that user\'s addresses', function () {
    $user  = User::factory()->create();
    $other = User::factory()->create();
    $order = makeOrderForUser($other);
    makeAddressForOrder($other, $order);

    expect($user->addresses)->toHaveCount(0);
});
