<?php

use App\Enums\OrderStatusEnum;
use App\Enums\ReviewStatusEnum;
use App\Enums\RolesEnum;
use App\Filament\Resources\Reviews\ReviewResource;
use App\Livewire\ProductDetailPage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Services\ReviewService;
use App\Services\UiService;
use Mockery\MockInterface;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->mock(UiService::class, function (MockInterface $mock) {
        $mock->shouldReceive('showMessage')->andReturn();
        $mock->shouldReceive('addToCartError')->andReturn();
    });
});

function createAdminUser(): User
{
    $role = Role::firstOrCreate(['name' => RolesEnum::ROLE_ADMIN->value]);
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function createModerationBuyerUser(): User
{
    $role = Role::firstOrCreate(['name' => RolesEnum::ROLE_BUYER->value]);
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function createModerationDeliveredOrderFor(User $user, Product $product): Order
{
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'order_status' => OrderStatusEnum::Delivered->value,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_amount' => $product->price,
        'total_amount' => $product->price,
    ]);

    return $order;
}

it('lets an admin hide a review, removing it from the public list and recalculating the average rating', function () {
    $admin = createAdminUser();
    $buyer = createModerationBuyerUser();
    $product = Product::factory()->create(['is_active' => true]);
    createModerationDeliveredOrderFor($buyer, $product);

    actingAs($buyer);
    livewire(ProductDetailPage::class, ['slug' => $product->slug])
        ->set('rating', 5)
        ->set('comment', 'Great product, works perfectly.')
        ->call('submitReview');

    $review = Review::where('user_id', $buyer->id)->where('product_id', $product->id)->firstOrFail();
    expect((float) $product->refresh()->average_rating)->toBe(5.0);

    actingAs($admin);
    app(ReviewService::class)->updateReviewStatus($review->fresh(), ReviewStatusEnum::HIDDEN);

    $this->assertDatabaseHas('reviews', [
        'id' => $review->id,
        'status' => ReviewStatusEnum::HIDDEN->value,
    ]);

    expect($product->refresh()->average_rating)->toBeNull();
    expect($product->reviews_count)->toBe(0);

    livewire(ProductDetailPage::class, ['slug' => $product->slug])
        ->assertDontSee('Great product, works perfectly.');
});

it('lets an admin add a public reply to a review, visible on ProductDetailPage', function () {
    $admin = createAdminUser();
    $buyer = createModerationBuyerUser();
    $product = Product::factory()->create(['is_active' => true]);
    createModerationDeliveredOrderFor($buyer, $product);

    actingAs($buyer);
    livewire(ProductDetailPage::class, ['slug' => $product->slug])
        ->set('rating', 4)
        ->set('comment', 'Solid product, does the job.')
        ->call('submitReview');

    $review = Review::where('user_id', $buyer->id)->where('product_id', $product->id)->firstOrFail();

    actingAs($admin);
    app(ReviewService::class)->updateAdminReply($review, 'Thanks for the feedback!');

    $this->assertDatabaseHas('reviews', [
        'id' => $review->id,
        'admin_reply' => 'Thanks for the feedback!',
    ]);

    livewire(ProductDetailPage::class, ['slug' => $product->slug])
        ->assertSee('Thanks for the feedback!');
});

it('does not let a buyer change the Admin Reply when editing their own review', function () {
    $admin = createAdminUser();
    $buyer = createModerationBuyerUser();
    $product = Product::factory()->create(['is_active' => true]);
    createModerationDeliveredOrderFor($buyer, $product);

    actingAs($buyer);
    livewire(ProductDetailPage::class, ['slug' => $product->slug])
        ->set('rating', 3)
        ->set('comment', 'It was okay.')
        ->call('submitReview');

    $review = Review::where('user_id', $buyer->id)->where('product_id', $product->id)->firstOrFail();

    actingAs($admin);
    app(ReviewService::class)->updateAdminReply($review, 'Appreciate the honest feedback.');

    actingAs($buyer);
    livewire(ProductDetailPage::class, ['slug' => $product->slug])
        ->set('rating', 5)
        ->set('comment', 'Actually it grew on me.')
        ->call('submitReview');

    $this->assertDatabaseHas('reviews', [
        'id' => $review->id,
        'rating' => 5,
        'admin_reply' => 'Appreciate the honest feedback.',
    ]);

    get(ReviewResource::getUrl('index'))->assertForbidden();
});

it('denies a non-admin access to the ReviewResource', function () {
    $buyer = createModerationBuyerUser();
    actingAs($buyer);

    get(ReviewResource::getUrl('index'))->assertForbidden();
});

it('allows an admin to access the ReviewResource', function () {
    $admin = createAdminUser();
    actingAs($admin);

    get(ReviewResource::getUrl('index'))->assertOk();
});
