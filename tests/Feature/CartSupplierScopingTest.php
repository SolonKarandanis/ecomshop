<?php

use App\Enums\RolesEnum;
use App\Livewire\ProductsPage;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Services\UiService;
use Mockery\MockInterface;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->mock(UiService::class, function (MockInterface $mock) {
        $mock->shouldReceive('showMessage')->andReturn();
        $mock->shouldReceive('addToCartError')->andReturn();
    });
});

function actingAsBuyer(): User
{
    $role = Role::firstOrCreate(['name' => RolesEnum::ROLE_BUYER->value]);
    $buyer = User::factory()->create();
    $buyer->assignRole($role);
    actingAs($buyer);

    return $buyer;
}

it('rejects adding a product from a different supplier to a buyer\'s cart', function () {
    actingAsBuyer();
    $supplierA = User::factory()->create();
    $supplierB = User::factory()->create();
    $productA = Product::factory()->create(['is_active' => true, 'supplier_id' => $supplierA->id]);
    $productB = Product::factory()->create(['is_active' => true, 'supplier_id' => $supplierB->id]);

    livewire(ProductsPage::class)
        ->call('addToCart', $productA->id)
        ->assertDispatched('cartUpdated');

    livewire(ProductsPage::class)
        ->call('addToCart', $productB->id)
        ->assertNotDispatched('cartUpdated');

    expect(Cart::first()->cartItems)->toHaveCount(1);
});

it('allows adding a second product from the same supplier to a buyer\'s cart', function () {
    actingAsBuyer();
    $supplier = User::factory()->create();
    $productA = Product::factory()->create(['is_active' => true, 'supplier_id' => $supplier->id]);
    $productB = Product::factory()->create(['is_active' => true, 'supplier_id' => $supplier->id]);

    livewire(ProductsPage::class)
        ->call('addToCart', $productA->id)
        ->assertDispatched('cartUpdated');

    livewire(ProductsPage::class)
        ->call('addToCart', $productB->id)
        ->assertDispatched('cartUpdated');

    expect(Cart::first()->cartItems)->toHaveCount(2);
});

it('rejects adding a product from a different supplier to a guest cart', function () {
    $supplierA = User::factory()->create();
    $supplierB = User::factory()->create();
    $productA = Product::factory()->create(['is_active' => true, 'supplier_id' => $supplierA->id]);
    $productB = Product::factory()->create(['is_active' => true, 'supplier_id' => $supplierB->id]);

    livewire(ProductsPage::class)
        ->call('addToCart', $productA->id)
        ->assertDispatched('cartUpdated');

    livewire(ProductsPage::class)
        ->call('addToCart', $productB->id)
        ->assertNotDispatched('cartUpdated');
});

it('allows adding a second product from the same supplier to a guest cart', function () {
    $supplier = User::factory()->create();
    $productA = Product::factory()->create(['is_active' => true, 'supplier_id' => $supplier->id]);
    $productB = Product::factory()->create(['is_active' => true, 'supplier_id' => $supplier->id]);

    livewire(ProductsPage::class)
        ->call('addToCart', $productA->id)
        ->assertDispatched('cartUpdated');

    livewire(ProductsPage::class)
        ->call('addToCart', $productB->id)
        ->assertDispatched('cartUpdated');
});
