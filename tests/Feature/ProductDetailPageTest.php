<?php
use App\Enums\RolesEnum;
use App\Livewire\ProductDetailPage;
use App\Models\Attribute;
use App\Models\AttributeOptions;
use App\Models\Product;
use App\Models\ProductAttributeValues;
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

it('renders the product detail page', function () {
    $product = Product::factory()->create(['is_active' => true, 'price' => 200]);
    $this->get(route('product.detail', $product->slug))
        ->assertSeeLivewire(ProductDetailPage::class)
        ->assertStatus(200)
        ->assertSee($product->name)
        ->assertSee('200')
        ->assertSee($product->description);
});

it('can add product to cart from detail page for buyer', function () {
    $role = Role::firstOrCreate(['name' => RolesEnum::ROLE_BUYER->value]);
    $user = User::factory()->create();
    $user->assignRole($role);
    actingAs($user);
    $product = Product::factory()->create(['is_active' => true]);
    livewire(ProductDetailPage::class, ['slug' => $product->slug])
        ->call('addToCart', $product->id, 1, [])
        ->assertDispatched('cartUpdated');
});

it('can add product to cart from detail page for guest', function () {
    $product = Product::factory()->create(['is_active' => true]);
    livewire(ProductDetailPage::class, ['slug' => $product->slug])
        ->call('addToCart', $product->id, 1, [])
        ->assertDispatched('cartUpdated');
});

it('shows product attributes', function () {
    $product = Product::factory()->create(['is_active' => true]);

    $attribute = Attribute::create(['name' => 'attribute.color', 'type' => 'Select']);
    $option = AttributeOptions::create(['attribute_id' => $attribute->id, 'option_name' => 'Red']);

    ProductAttributeValues::create([
        'product_id' => $product->id,
        'attribute_id' => $attribute->id,
        'attribute_option_id' => $option->id,
    ]);

    livewire(ProductDetailPage::class, ['slug' => $product->slug])
        ->assertSet('hasColorAttribute', true)
        ->assertSet('hasPanelTypeAttribute', false);
});

it('blocks non-buyer users from adding to cart', function () {
    $user = User::factory()->create();
    actingAs($user);

    $product = Product::factory()->create(['is_active' => true]);
    livewire(ProductDetailPage::class, ['slug' => $product->slug])
        ->call('addToCart', $product->id, 1, [])
        ->assertNotDispatched('cartUpdated');
});
