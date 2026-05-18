# Plan: Product Reviews & Ratings

## Context

The e-commerce storefront has a complete purchase flow but no way for buyers to leave feedback on products. Adding reviews builds trust, gives buyers a voice, and gives the admin team a moderation surface. The architecture is a strict Laravel layered stack: Livewire pages → Services → Repositories → Models; business logic must not bleed into Livewire components.

---

## Files to Create

| Path | Purpose |
|------|---------|
| `database/migrations/2026_05_15_000001_create_reviews_table.php` | `reviews` table |
| `app/Models/Review.php` | Eloquent model |
| `app/Enums/ReviewRatingEnum.php` | Rating 1–5 |
| `app/Dtos/CreateReviewDto.php` | DTO for review submission |
| `app/Exceptions/ReviewException.php` | Domain exception |
| `app/Repositories/ReviewRepository.php` | Data access |
| `app/Services/ReviewService.php` | Business logic |
| `app/Filament/Resources/Reviews/ReviewResource.php` | Filament admin resource |
| `app/Filament/Resources/Reviews/Pages/ListReviews.php` | |
| `app/Filament/Resources/Reviews/Pages/ViewReview.php` | |
| `app/Filament/Resources/Reviews/Schemas/ReviewInfolist.php` | |
| `app/Filament/Resources/Reviews/Tables/ReviewsTable.php` | |
| `app/Filament/Resources/Products/RelationManagers/ReviewsRelationManager.php` | Per-product reviews in admin |

## Files to Modify

| Path | Change |
|------|--------|
| `app/Models/Product.php` | Add `reviews()` hasMany |
| `app/Models/User.php` | Add `reviews()` hasMany |
| `app/Providers/AppServiceProvider.php` | Register `ReviewService` singleton |
| `app/Livewire/ProductDetailPage.php` | Inject `ReviewService`, add form properties + `submitReview()` |
| `resources/views/livewire/product-detail-page.blade.php` | Add reviews section below line 216 |
| `app/Filament/Resources/Products/ProductResource.php` | Add `ReviewsRelationManager` to `getRelations()` |

---

## Implementation Steps

### 1. Migration

```php
Schema::create('reviews', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
    $table->unsignedTinyInteger('rating');     // 1–5, enforced by validation
    $table->text('body');                      // required, min 10 chars
    $table->timestamps();
    $table->unique(['user_id', 'product_id']); // one review per buyer per product
    $table->index(['product_id', 'created_at']);
});
```

### 2. Model — `app/Models/Review.php`

- `$fillable`: `['user_id', 'product_id', 'rating', 'body']`
- `$casts`: `['rating' => 'integer']`
- `user()` belongsTo User, `product()` belongsTo Product

Add to `Product`:
```php
public function reviews(): HasMany
{
    return $this->hasMany(Review::class);
}
```

Add to `User`:
```php
public function reviews(): HasMany
{
    return $this->hasMany(Review::class);
}
```

### 3. Enum — `app/Enums/ReviewRatingEnum.php`

```php
enum ReviewRatingEnum: int {
    case One   = 1;
    case Two   = 2;
    case Three = 3;
    case Four  = 4;
    case Five  = 5;

    public static function options(): array
    {
        return [1 => '1 star', 2 => '2 stars', 3 => '3 stars', 4 => '4 stars', 5 => '5 stars'];
    }
}
```

### 4. DTO — `app/Dtos/CreateReviewDto.php`

Fields: `userId`, `productId`, `rating`, `body`. Static `fromArray(array $data): self`. Explicit getters/setters matching the `CheckoutDTO` style.

### 5. Exception — `app/Exceptions/ReviewException.php`

```php
class ReviewException extends \Exception {}
```

### 6. Repository — `app/Repositories/ReviewRepository.php`

```php
public function getReviewsForProduct(int $productId, int $perPage = 10): LengthAwarePaginator
// with('user:id,name'), orderBy created_at desc

public function getProductRatingSummary(int $productId): array
// returns ['average' => float|null (1 dp), 'count' => int]

public function userHasReviewed(int $userId, int $productId): bool

public function userHasPurchasedProduct(int $userId, int $productId): bool
// DB::table('order_items')->join('orders', ...)
// ->where('orders.user_id', $userId)
// ->where('order_items.product_id', $productId)
// ->where('orders.order_status', '!=', OrderStatusEnum::Cancelled->value)
// ->exists()

public function createReview(CreateReviewDto $dto): Review
```

### 7. Service — `app/Services/ReviewService.php`

```php
public function __construct(private readonly ReviewRepository $reviewRepository) {}

public function canReview(int $userId, int $productId): bool
// false if already reviewed OR has not purchased

public function submitReview(CreateReviewDto $dto): Review
// calls canReview(), throws ReviewException if ineligible

public function getReviewsForProduct(int $productId, int $perPage = 10): LengthAwarePaginator

public function getRatingSummary(int $productId): array
```

Register in `AppServiceProvider::register()`:
```php
$this->app->singleton(ReviewService::class, function ($app) {
    return new ReviewService($app->make(ReviewRepository::class));
});
```

### 8. ProductDetailPage.php changes

Inject in `boot()`:
```php
public function boot(
    ProductRepository $productRepository,
    CartService $cartService,
    UiService $uiService,
    ReviewService $reviewService,
): void { ... }
```

Add public properties:
```php
public int    $productId    = 0;   // set in mount()
public int    $reviewRating = 5;
public string $reviewBody   = '';
public bool   $canReview    = false;
```

In `mount()`: resolve product by slug, store `$this->productId = $product->id`.

In `render()`: pass `$ratingSummary` and `$reviews` to view; set `$this->canReview` from `ReviewService::canReview()`.

New action method:
```php
public function submitReview(): void
{
    $this->validate([
        'reviewRating' => ['required', 'integer', 'min:1', 'max:5'],
        'reviewBody'   => ['required', 'string', 'min:10', 'max:2000'],
    ]);
    $dto = CreateReviewDto::fromArray([
        'userId'    => auth()->id(),
        'productId' => $this->productId,
        'rating'    => $this->reviewRating,
        'body'      => $this->reviewBody,
    ]);
    try {
        $this->reviewService->submitReview($dto);
        $this->reset(['reviewRating', 'reviewBody']);
        $this->canReview = false;
        $this->uiService->showMessage(MessageSeverityEnum::SUCCESS, 'Review submitted', 'Thank you!');
    } catch (ReviewException $e) {
        $this->uiService->showMessage(MessageSeverityEnum::ERROR, 'Error', $e->getMessage());
    }
}
```

### 9. Blade view — `product-detail-page.blade.php`

Insert a `<section>` block between line 216 (`</section>`) and line 217 (`</div>`):

- **Rating summary bar**: average stars + count from `$ratingSummary`
- **Reviews list**: loop `$reviews` — reviewer first name, star display, `diffForHumans()` date, body text. Paginate with `{{ $reviews->links() }}`
- **Review form** (conditional):
  - `@auth` + `$canReview` → 5-star radio inputs (`wire:model="reviewRating"`) + textarea (`wire:model.defer="reviewBody"`) + submit button (`wire:click="submitReview"`)
  - `@auth` + `!$canReview` → "Only verified buyers who purchased this product may leave a review."
  - `@guest` → "Log in to leave a review."

### 10. Filament Admin — `app/Filament/Resources/Reviews/`

Structure mirrors `Orders/` exactly (no Create/Edit pages — admins only view and delete):

- **`ReviewResource.php`** — `$model = Review::class`, `navigationIcon = Heroicon::Star`, `navigationGroup = 'Catalog'`, pages: `index` + `view` only, `getNavigationBadge()` returns count
- **`Pages/ListReviews.php`** — extends `ListRecords`, no `CreateAction` in header actions
- **`Pages/ViewReview.php`** — extends `ViewRecord`, header action: `DeleteAction` only
- **`Tables/ReviewsTable.php`** — columns: `product.name`, `user.name`, `rating`, `body` (limit 60), `created_at`; filter by rating; record actions: `ViewAction` + `DeleteAction`; bulk `DeleteBulkAction`
- **`Schemas/ReviewInfolist.php`** — entries: `product.name`, `user.name`, `rating`, `body` (columnSpanFull), `created_at`, `updated_at`

### 11. ReviewsRelationManager on ProductResource

`app/Filament/Resources/Products/RelationManagers/ReviewsRelationManager.php` — `protected static string $relationship = 'reviews'`; columns: `user.name`, `rating`, `body` (limit 60), `created_at`; action: `DeleteAction`.

Add to `ProductResource::getRelations()`:
```php
return [ReviewsRelationManager::class];
```

---

## Eligibility Rules

A buyer can review a product **only if**:
1. They are authenticated
2. They have at least one non-cancelled order containing that product (`OrderStatusEnum::Cancelled->value = 'order.status.cancelled'`)
3. They have not already reviewed it

---

## Verification

```bash
# Run migration
php artisan migrate

# Run all tests
composer run test

# Run feature-specific tests
php artisan test --filter=ReviewsTest
```

Manual verification:
1. `/products/{slug}` as guest — reviews section visible, form absent
2. Log in as a buyer who has NOT ordered the product — form absent, ineligibility message shown
3. Log in as a buyer who HAS ordered the product — form visible, submit a review
4. Attempt to submit a second review — error toast
5. `/admin/reviews` — review appears, delete action works
6. `/admin/products/{id}` — Reviews relation manager shows the review
