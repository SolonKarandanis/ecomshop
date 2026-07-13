<?php

use App\Enums\OrderStatusEnum;
use App\Enums\RolesEnum;
use App\Livewire\ProductDetailPage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
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

function createBuyerUser(): User
{
    $role = Role::firstOrCreate(['name' => RolesEnum::ROLE_BUYER->value]);
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function createDeliveredOrderFor(User $user, Product $product): Order
{
    $order = Order::factory()->create([
        'user_id'      => $user->id,
        'order_status' => OrderStatusEnum::Delivered->value,
    ]);

    OrderItem::create([
        'order_id'    => $order->id,
        'product_id'  => $product->id,
        'quantity'    => 1,
        'unit_amount' => $product->price,
        'total_amount' => $product->price,
    ]);

    return $order;
}

it('allows a buyer with a delivered order to submit a review and see it appear', function () {
    $buyer = createBuyerUser();
    $product = Product::factory()->create(['is_active' => true]);
    createDeliveredOrderFor($buyer, $product);
    actingAs($buyer);

    livewire(ProductDetailPage::class, ['slug' => $product->slug])
        ->set('rating', 5)
        ->set('comment', 'Great product, works perfectly.')
        ->call('submitReview')
        ->assertSet('rating', 0)
        ->assertSet('comment', null)
        ->assertSee('Great product, works perfectly.');

    $this->assertDatabaseHas('reviews', [
        'user_id'    => $buyer->id,
        'product_id' => $product->id,
        'rating'     => 5,
        'comment'    => 'Great product, works perfectly.',
    ]);
});

it('does not allow a buyer without a verified purchase to submit a review', function () {
    $buyer = createBuyerUser();
    $product = Product::factory()->create(['is_active' => true]);
    actingAs($buyer);

    livewire(ProductDetailPage::class, ['slug' => $product->slug])
        ->set('rating', 4)
        ->set('comment', 'Never even received this.')
        ->call('submitReview');

    $this->assertDatabaseMissing('reviews', [
        'user_id'    => $buyer->id,
        'product_id' => $product->id,
    ]);
    expect(Review::count())->toBe(0);
});

it('does not allow a buyer to submit a second review for the same product', function () {
    $buyer = createBuyerUser();
    $product = Product::factory()->create(['is_active' => true]);
    createDeliveredOrderFor($buyer, $product);
    actingAs($buyer);

    Review::create([
        'user_id'    => $buyer->id,
        'product_id' => $product->id,
        'rating'     => 3,
        'comment'    => 'First review.',
    ]);

    livewire(ProductDetailPage::class, ['slug' => $product->slug])
        ->set('rating', 5)
        ->set('comment', 'Second attempt.')
        ->call('submitReview');

    expect(Review::where('user_id', $buyer->id)->where('product_id', $product->id)->count())->toBe(1);
    $this->assertDatabaseMissing('reviews', [
        'user_id'    => $buyer->id,
        'product_id' => $product->id,
        'comment'    => 'Second attempt.',
    ]);
});
