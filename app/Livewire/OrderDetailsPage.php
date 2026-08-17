<?php

namespace App\Livewire;

use App\Attributes\PreAuthorize;
use App\Dtos\SubmitReviewDto;
use App\Dtos\UpdateReviewDTO;
use App\Enums\OrderStatusEnum;
use App\Exceptions\OrderException;
use App\Exceptions\ReviewException;
use App\Http\Requests\SubmitReviewRequest;
use App\Livewire\Traits\WithMessages;
use App\Livewire\Traits\WithPreAuthorize;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\ReviewService;
use App\Services\UiService;
use App\Traits\HasStatusClasses;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Order Details')]
class OrderDetailsPage extends Component
{
    use HasStatusClasses, WithMessages, WithPreAuthorize;

    public $id;

    public ?int $reviewingProductId = null;

    public int $rating = 0;

    public ?string $comment = null;

    public ?int $reviewId = null;

    protected OrderService $orderService;

    protected UiService $uiService;

    protected ReviewService $reviewService;

    public function boot(
        OrderService $orderService,
        UiService $uiService,
        ReviewService $reviewService
    ): void {
        $this->orderService = $orderService;
        $this->uiService = $uiService;
        $this->reviewService = $reviewService;
    }

    public function mount($id): void
    {
        $this->id = $id;
    }

    #[Computed]
    public function order(): Order
    {
        return $this->orderService->getOrderById($this->id, auth()->user());
    }

    #[Computed]
    public function canSupplierActOnOrder(): bool
    {
        return auth()->check() && $this->orderService->canSupplierActOn($this->order, auth()->user());
    }

    public function openReviewForm(int $productId): void
    {
        if ($this->reviewingProductId === $productId) {
            $this->reviewingProductId = null;

            return;
        }

        $this->reviewingProductId = $productId;
        $review = $this->reviewService->getReviewForBuyer(auth()->id(), $productId);

        $this->reviewId = $review?->id;
        $this->rating = $review?->rating ?? 0;
        $this->comment = $review?->comment;
    }

    public function canReviewProduct(int $productId): bool
    {
        return auth()->check() && auth()->user()->isBuyer()
            && $this->reviewService->canReview(auth()->id(), $productId);
    }

    #[PreAuthorize('buyer-action')]
    public function submitReview(): void
    {
        $isEditing = $this->reviewId !== null;
        $title = $isEditing ? __('messages.submit_review.edit_title') : __('messages.submit_review.title');

        if (! $this->isPreAuthorized(__FUNCTION__) || ! ($isEditing || $this->canReviewProduct($this->reviewingProductId))) {
            $this->handleError($title, __('messages.submit_review.not_eligible'), ReviewException::notEligible());

            return;
        }

        $request = new SubmitReviewRequest;
        $request->merge([
            'rating' => $this->rating,
            'comment' => $this->comment,
        ]);
        $this->validate($request->rules());

        try {
            if ($isEditing) {
                $dto = UpdateReviewDTO::fromRequest($request, $this->reviewId, auth()->id(), $this->reviewingProductId);
                $this->reviewService->updateReview($dto);
                $this->handleSuccess(null, $title, __('messages.submit_review.update_success'));
            } else {
                $dto = SubmitReviewDto::fromRequest($request, $this->reviewingProductId, auth()->id());
                $this->reviewService->submitReview($dto);
                $this->reset('rating', 'comment');
                $this->handleSuccess(null, $title, __('messages.submit_review.success'));
            }
        } catch (ReviewException $e) {
            $this->handleError($title, __('messages.submit_review.error'), $e);
        }
    }

    #[PreAuthorize('supplier-action')]
    public function markAsShipped(): void
    {
        $this->transitionOrderAsSupplier(__FUNCTION__, OrderStatusEnum::Shipped, __('messages.supplier_order_actions.shipped_success'));
    }

    #[PreAuthorize('supplier-action')]
    public function markAsDelivered(): void
    {
        $this->transitionOrderAsSupplier(__FUNCTION__, OrderStatusEnum::Delivered, __('messages.supplier_order_actions.delivered_success'));
    }

    #[PreAuthorize('supplier-action')]
    public function cancelOrder(): void
    {
        $this->transitionOrderAsSupplier(__FUNCTION__, OrderStatusEnum::Cancelled, __('messages.supplier_order_actions.cancelled_success'));
    }

    private function transitionOrderAsSupplier(string $callingMethod, OrderStatusEnum $toStatus, string $successMessage): void
    {
        $title = __('messages.supplier_order_actions.title');

        if (! $this->isPreAuthorized($callingMethod) || ! $this->canSupplierActOnOrder) {
            $this->handleError($title, __('messages.supplier_order_actions.not_eligible'), OrderException::supplierActionNotAllowed());

            return;
        }

        try {
            $this->orderService->transitionOrderStatusBySupplier($this->order, auth()->user(), $toStatus);
            unset($this->order, $this->canSupplierActOnOrder);
            $this->handleSuccess(null, $title, $successMessage);
        } catch (OrderException $e) {
            $this->handleError($title, __('messages.supplier_order_actions.error'), $e);
        }
    }

    public function render()
    {
        return view('livewire.order-details-page', [
            'order' => $this->order,
            'canSupplierActOnOrder' => $this->canSupplierActOnOrder,
        ]);
    }
}
