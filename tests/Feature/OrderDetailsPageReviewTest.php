<?php

use App\Enums\OrderStatusEnum;
use App\Enums\RolesEnum;
use App\Livewire\OrderDetailsPage;
use App\Livewire\ProductDetailPage;
use App\Models\Address;
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
    });
});

function createOrderDetailsBuyer(): User
{
    $role = Role::firstOrCreate(['name' => RolesEnum::ROLE_BUYER->value]);
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function createOrderForBuyer(User $user, Product $product, string $orderStatus): Order
{
    $order = Order::factory()->create([
        'user_id'      => $user->id,
        'order_status' => $orderStatus,
    ]);

    OrderItem::create([
        'order_id'     => $order->id,
        'product_id'   => $product->id,
        'quantity'     => 1,
        'unit_amount'  => $product->price,
        'total_amount' => $product->price,
    ]);

    Address::create([
        'user_id'        => $user->id,
        'order_id'       => $order->id,
        'first_name'     => 'Test',
        'last_name'      => 'Buyer',
        'phone'          => '5555555555',
        'street_address' => '123 Main St',
        'city'           => 'Athens',
        'country'        => 'GR',
        'postal_code'    => '12345',
    ]);

    return $order;
}

it('allows a buyer to submit a review from OrderDetailsPage for a delivered order item', function () {
    $buyer = createOrderDetailsBuyer();
    $product = Product::factory()->create(['is_active' => true]);
    $order = createOrderForBuyer($buyer, $product, OrderStatusEnum::Delivered->value);
    actingAs($buyer);

    livewire(OrderDetailsPage::class, ['id' => $order->id])
        ->call('openReviewForm', $product->id)
        ->set('rating', 5)
        ->set('comment', 'Great product, exactly as described.')
        ->call('submitReview')
        ->assertSet('rating', 0)
        ->assertSet('comment', null);

    $this->assertDatabaseHas('reviews', [
        'user_id'    => $buyer->id,
        'product_id' => $product->id,
        'rating'     => 5,
        'comment'    => 'Great product, exactly as described.',
    ]);
    expect(Review::where('user_id', $buyer->id)->where('product_id', $product->id)->count())->toBe(1);
});

it('shows the edit form on OrderDetailsPage for an existing review, and editing there updates the same review visible on ProductDetailPage', function () {
    $buyer = createOrderDetailsBuyer();
    $product = Product::factory()->create(['is_active' => true]);
    $order = createOrderForBuyer($buyer, $product, OrderStatusEnum::Delivered->value);

    $review = Review::create([
        'user_id'    => $buyer->id,
        'product_id' => $product->id,
        'rating'     => 3,
        'comment'    => 'First review.',
    ]);

    actingAs($buyer);

    livewire(OrderDetailsPage::class, ['id' => $order->id])
        ->call('openReviewForm', $product->id)
        ->assertSet('reviewId', $review->id)
        ->assertSet('rating', 3)
        ->assertSet('comment', 'First review.')
        ->set('rating', 5)
        ->set('comment', 'Updated after using it more.')
        ->call('submitReview');

    expect(Review::where('user_id', $buyer->id)->where('product_id', $product->id)->count())->toBe(1);
    $this->assertDatabaseHas('reviews', [
        'id'      => $review->id,
        'rating'  => 5,
        'comment' => 'Updated after using it more.',
    ]);
    expect((float) $product->refresh()->average_rating)->toBe(5.0);

    livewire(ProductDetailPage::class, ['slug' => $product->slug])
        ->assertSet('reviewId', $review->id)
        ->assertSet('rating', 5)
        ->assertSet('comment', 'Updated after using it more.')
        ->assertSee('Updated after using it more.');
});

it('does not show the review action for order items on orders that are not yet delivered', function () {
    $buyer = createOrderDetailsBuyer();
    $product = Product::factory()->create(['is_active' => true]);
    $order = createOrderForBuyer($buyer, $product, OrderStatusEnum::Paid->value);
    actingAs($buyer);

    livewire(OrderDetailsPage::class, ['id' => $order->id])
        ->assertDontSee(__('order-page.review_this_product'));
});
