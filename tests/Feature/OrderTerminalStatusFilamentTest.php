<?php

use App\Enums\OrderStatusEnum;
use App\Enums\RolesEnum;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Models\Order;
use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

function createTerminalStatusAdmin(): User
{
    $role = Role::firstOrCreate(['name' => RolesEnum::ROLE_ADMIN->value]);
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

it('does not allow an admin to move a Delivered order to any other status via Filament', function () {
    $admin = createTerminalStatusAdmin();
    $order = Order::factory()->create(['order_status' => OrderStatusEnum::Delivered->value]);
    actingAs($admin);

    livewire(EditOrder::class, ['record' => $order->getKey()])
        ->fillForm(['order_status' => OrderStatusEnum::Cancelled->value])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($order->refresh()->order_status)->toBe(OrderStatusEnum::Delivered->value);
});

it('does not allow an admin to move a Cancelled order to any other status via Filament', function () {
    $admin = createTerminalStatusAdmin();
    $order = Order::factory()->create(['order_status' => OrderStatusEnum::Cancelled->value]);
    actingAs($admin);

    livewire(EditOrder::class, ['record' => $order->getKey()])
        ->fillForm(['order_status' => OrderStatusEnum::Shipped->value])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($order->refresh()->order_status)->toBe(OrderStatusEnum::Cancelled->value);
});

it('still allows an admin to edit non-status fields on a terminal order', function () {
    $admin = createTerminalStatusAdmin();
    $order = Order::factory()->create(['order_status' => OrderStatusEnum::Delivered->value, 'notes' => 'Original notes']);
    actingAs($admin);

    livewire(EditOrder::class, ['record' => $order->getKey()])
        ->fillForm(['notes' => 'Updated notes'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($order->refresh())
        ->notes->toBe('Updated notes')
        ->order_status->toBe(OrderStatusEnum::Delivered->value);
});

it('still allows an admin to change order_status normally on a non-terminal order', function () {
    $admin = createTerminalStatusAdmin();
    $order = Order::factory()->create(['order_status' => OrderStatusEnum::Paid->value]);
    actingAs($admin);

    livewire(EditOrder::class, ['record' => $order->getKey()])
        ->fillForm(['order_status' => OrderStatusEnum::Shipped->value])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($order->refresh()->order_status)->toBe(OrderStatusEnum::Shipped->value);
});
