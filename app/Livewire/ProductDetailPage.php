<?php

namespace App\Livewire;

use App\Attributes\PreAuthorize;
use App\Dtos\SubmitReviewDto;
use App\Exceptions\ReviewException;
use App\Http\Requests\SubmitReviewRequest;
use App\Livewire\Traits\WithCartActions;
use App\Livewire\Traits\WithPreAuthorize;
use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Services\CartService;
use App\Services\ReviewService;
use App\Services\UiService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class ProductDetailPage extends Component
{
    use WithCartActions, WithPagination, WithPreAuthorize;
    public string $slug;

    public bool $hasColorAttribute = false;
    public bool $hasPanelTypeAttribute = false;
    public bool $hasHardDriveAttribute = false;
    public bool $hasKeyboardAttribute = false;
    public bool $hasRamAttribute = false;
    public bool $hasGpuAttribute = false;
    public int $rating = 0;
    public ?string $comment = null;

    protected ProductRepository $productRepository;
    protected CartService $cartService;
    protected UiService $uiService;
    protected ReviewService $reviewService;

    public function boot(
        ProductRepository $productRepository,
        CartService $cartService,
        UiService $uiService,
        ReviewService $reviewService
    ): void{
        $this->productRepository = $productRepository;
        $this->cartService = $cartService;
        $this->uiService = $uiService;
        $this->reviewService = $reviewService;
    }

    public function mount(string $slug): void
    {
        $this->slug = $slug;
    }

    #[Computed]
    public function product(): Product
    {
        return $this->productRepository->getProductBySlug($this->slug);
    }

    #[Computed]
    public function reviews(): LengthAwarePaginator|Collection
    {
        return $this->reviewService->getPublishedReviewsForProduct($this->product->id);
    }

    #[Computed]
    public function canReviewProduct(): bool
    {
        if (!auth()->check() || !auth()->user()->isBuyer()) {
            return false;
        }
        return $this->reviewService->canReview(auth()->id(), $this->product->id);
    }

    #[PreAuthorize('buyer-action')]
    public function submitReview(): void
    {
        $title = __('messages.submit_review.title');

        if (!$this->isPreAuthorized(__FUNCTION__) || !$this->canReviewProduct) {
            $this->handleError($title, __('messages.submit_review.not_eligible'), ReviewException::notEligible());
            return;
        }

        $request = new SubmitReviewRequest();
        $request->merge([
            'rating' => $this->rating,
            'comment' => $this->comment,
        ]);
        $this->validate($request->rules());

        $dto = SubmitReviewDto::fromRequest($request, $this->product->id, auth()->id());

        try {
            $this->reviewService->submitReview($dto);
            unset($this->product);
            $this->reset('rating', 'comment');
            $this->handleSuccess(null, $title, __('messages.submit_review.success'));
        } catch (ReviewException $e) {
            $this->handleError($title, __('messages.submit_review.error'), $e);
        }
    }

    public function render()
    {
        $product = $this->product;
        $this->hasColorAttribute      = $product->colorAttributeValues->count() > 0;
        $this->hasPanelTypeAttribute  = $product->panelTypeAttributeValues->count() > 0;
        $this->hasHardDriveAttribute  = $product->hardDriveAttributeValues->count() > 0;
        $this->hasKeyboardAttribute   = $product->keyboardAttributeValues->count() > 0;
        $this->hasRamAttribute        = $product->ramAttributeValues->count() > 0;
        $this->hasGpuAttribute        = $product->gpuAttributeValues->count() > 0;
        $reviews = $this->reviews;
        $canReviewProduct = $this->canReviewProduct;
        return view('livewire.product-detail-page', compact('product', 'reviews', 'canReviewProduct'));
    }
}
