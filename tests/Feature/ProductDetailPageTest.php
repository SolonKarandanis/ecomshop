<?php
use App\Livewire\ProductDetailPage;
use App\Models\Attribute;
use App\Models\AttributeOptions;
use App\Models\Product;
use App\Models\ProductAttributeValues;
use Illuminate\Support\Number;
use function Pest\Livewire\livewire;

it('renders the product detail page', function () {
    $product = Product::factory()->create(['is_active' => true, 'price' => 200]);
    $this->get(route('product.detail', $product->slug))
        ->assertSeeLivewire(ProductDetailPage::class)
        ->assertStatus(200)
        ->assertSee($product->name)
        ->assertSee('200')
        ->assertSee($product->description);
});

it('can add product to cart from detail page', function () {
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
