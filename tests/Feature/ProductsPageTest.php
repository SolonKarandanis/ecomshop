<?php

use App\Enums\RolesEnum;
use App\Livewire\ProductsPage;
use App\Models\Product;
use App\Models\User;
use App\Services\UiService;
use Illuminate\Support\Number;
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

it('renders the products page', function () {
    $this->get('/products')
        ->assertSeeLivewire(ProductsPage::class)
        ->assertStatus(200);
});

it('shows a list of products', function () {
    $products = Product::factory()->count(3)->create(['is_active' => true]);

    livewire(ProductsPage::class)
        ->assertSee($products->first()->name)
        ->assertSee($products->last()->name);
});

it('can filter products by category', function () {
    $product1 = Product::factory()->create(['is_active' => true]);
    $product2 = Product::factory()->create(['is_active' => true]);

    livewire(ProductsPage::class)
        ->set('selected_categories', [$product1->category->id])
        ->assertSee($product1->name)
        ->assertDontSee($product2->name);
});

it('can filter products by brand', function () {
    $product1 = Product::factory()->create(['is_active' => true]);
    $product2 = Product::factory()->create(['is_active' => true]);

    livewire(ProductsPage::class)
        ->set('selected_brands', [$product1->brand->id])
        ->assertSee($product1->name)
        ->assertDontSee($product2->name);
});

it('can filter products by price', function () {
    $product1 = Product::factory()->create(['price' => 100, 'is_active' => true]);
    $product2 = Product::factory()->create(['price' => 200, 'is_active' => true]);

    livewire(ProductsPage::class)
        ->set('price_from', 50)
        ->set('price_to', 150)
        ->assertSee($product1->name)
        ->assertDontSee($product2->name);
});

it('can sort products by latest', function () {
    $oldProduct = Product::factory()->create(['name' => 'Old Product', 'created_at' => now()->subDay(), 'is_active' => true]);
    $newProduct = Product::factory()->create(['name' => 'New Product', 'created_at' => now(), 'is_active' => true]);

    livewire(ProductsPage::class)
        ->set('sort', 'latest')
        ->assertSeeInOrder([$newProduct->name, $oldProduct->name]);
});

it('can sort products by oldest', function () {
    $oldProduct = Product::factory()->create(['name' => 'Old Product', 'created_at' => now()->subDay(), 'is_active' => true]);
    $newProduct = Product::factory()->create(['name' => 'New Product', 'created_at' => now(), 'is_active' => true]);

    livewire(ProductsPage::class)
        ->set('sort', 'oldest')
        ->assertSeeInOrder([$oldProduct->name, $newProduct->name]);
});

it('can add product to cart for buyer', function () {
    $role = Role::firstOrCreate(['name' => RolesEnum::ROLE_BUYER->value]);
    $user = User::factory()->create();
    $user->assignRole($role);
    actingAs($user);
    $product = Product::factory()->create(['is_active' => true]);

    livewire(ProductsPage::class)
        ->call('addToCart', $product->id)
        ->assertDispatched('cartUpdated');
});

it('can add product to cart for guest', function () {
    $product = Product::factory()->create(['is_active' => true]);

    livewire(ProductsPage::class)
        ->call('addToCart', $product->id)
        ->assertDispatched('cartUpdated');
});

it('blocks non-buyer users from adding to cart', function () {
    $user = User::factory()->create();
    actingAs($user);

    $product = Product::factory()->create(['is_active' => true]);
    livewire(ProductsPage::class)
        ->call('addToCart', $product->id)
        ->assertNotDispatched('cartUpdated');
});
