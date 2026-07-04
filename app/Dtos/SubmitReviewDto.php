<?php

namespace App\Dtos;

class SubmitReviewDto
{
    private int $productId;
    private int $userId;
    private int $rating;
    private ?string $comment;

    public function __construct(int $productId, int $userId, int $rating, ?string $comment = null)
    {
        $this->productId = $productId;
        $this->userId = $userId;
        $this->rating = $rating;
        $this->comment = $comment;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getRating(): int
    {
        return $this->rating;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }
}
