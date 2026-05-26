<?php

use App\Dtos\ChangePasswordDto;
use App\Dtos\CreateUserDTO;
use App\Dtos\UpdateProfileDto;
use App\Enums\RolesEnum;
use App\Enums\UserStatusEnum;
use App\Exceptions\ProfileException;
use App\Models\Address;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Hash;
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

it('activateUser sets status to active and persists', function () {
    $user = User::factory()->create(['status' => UserStatusEnum::INACTIVE->value]);

    app(UserService::class)->activateUser($user);

    expect($user->fresh()->status)->toBe(UserStatusEnum::ACTIVE);
});

it('deactivateUser sets status to inactive and persists', function () {
    $user = User::factory()->create();

    app(UserService::class)->deactivateUser($user);

    expect($user->fresh()->status)->toBe(UserStatusEnum::INACTIVE);
});

it('updateProfile updates name and email', function () {
    $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);
    $dto  = UpdateProfileDto::fromArray(['name' => 'New Name', 'email' => 'new@example.com']);

    app(UserService::class)->updateProfile($user, $dto);

    expect($user->fresh()->name)->toBe('New Name')
        ->and($user->fresh()->email)->toBe('new@example.com');
});

it('updateProfile throws ProfileException when email belongs to another account', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create(['email' => 'mine@example.com']);
    $dto  = UpdateProfileDto::fromArray(['name' => $user->name, 'email' => 'taken@example.com']);

    expect(fn () => app(UserService::class)->updateProfile($user, $dto))
        ->toThrow(ProfileException::class);
});

it('changePassword updates password when current password is correct', function () {
    $user = User::factory()->create(['password' => bcrypt('oldpassword')]);
    $dto  = ChangePasswordDto::fromArray([
        'currentPassword'         => 'oldpassword',
        'newPassword'             => 'newpassword123',
        'newPasswordConfirmation' => 'newpassword123',
    ]);

    app(UserService::class)->changePassword($user, $dto);

    expect(Hash::check('newpassword123', $user->fresh()->password))->toBeTrue();
});

it('changePassword throws ProfileException when current password is wrong', function () {
    $user = User::factory()->create(['password' => bcrypt('correctpassword')]);
    $dto  = ChangePasswordDto::fromArray([
        'currentPassword'         => 'wrongpassword',
        'newPassword'             => 'newpassword123',
        'newPasswordConfirmation' => 'newpassword123',
    ]);

    expect(fn () => app(UserService::class)->changePassword($user, $dto))
        ->toThrow(ProfileException::class);
});
