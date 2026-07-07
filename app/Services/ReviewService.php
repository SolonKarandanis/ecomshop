<?php

namespace App\Services;

use App\Dtos\SubmitReviewDto;
use App\Exceptions\ReviewException;
use App\Models\Review;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ReviewRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ReviewService
{
    public function __construct(
        private readonly ReviewRepository $reviewRepository,
        private readonly OrderRepository $orderRepository,
        private readonly ProductRepository $productRepository,
    ){}

    public function canReview(int $userId, int $productId): bool
    {
        if ($this->reviewRepository->findByUserAndProduct($userId, $productId)){
            return false;
        }
        return $this->orderRepository->hasDeliveredOrderForProduct($userId, $productId);
    }

    /**
     * @throws ReviewException
     */
    public function submitReview(SubmitReviewDto $dto): Review
    {
        if (!$this->canReview($dto->getUserId(), $dto->getProductId())){
            throw ReviewException::notEligible();
        }
        $review = $this->reviewRepository->createReview($dto);
        $stats = $this->reviewRepository->getRatingStatsForProduct($dto->getProductId());
        $this->productRepository->updateRatingStats($dto->getProductId(), $stats->getAverageRating(), $stats->getReviewsCount());
        return $review;
    }

    public function getPublishedReviewsForProduct(int $productId): LengthAwarePaginator|Collection
    {
        return $this->reviewRepository->getPublishedReviewsForProduct($productId);
    }
}
