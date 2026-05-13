<?php

namespace App\Dtos;

class OrderSearchRequestDTO
{
    private int $userId;
    private ?string $orderStatus = null;
    private ?string $paymentStatus = null;
    private ?string $fromDate = null;
    private ?string $toDate = null;
    private ?float $minPrice = null;

    private ?float $maxPrice = null;
    private int $perPage = 5;

    public function __construct()
    {
        $this->userId = auth()->user()->id;
    }

    public static function fromArray(array $data): self{
        $instance = new self();
        $instance->withOrderStatus($data['orderStatus'] ?? null);
        $instance->withPaymentStatus($data['paymentStatus'] ?? null);
        $instance->withMinPrice($data['minPrice'] ?? null);
        $instance->withMaxPrice($data['maxPrice'] ?? null);
        $instance->withFromDate($data['fromDate'] ?? null);
        $instance->withToDate($data['toDate'] ?? null);
        return $instance;
    }

    public function withOrderStatus(string|null $orderStatus): self
    {
        $this->orderStatus = $orderStatus;
        return $this;
    }

    public function withPaymentStatus(string|null $paymentStatus): self
    {
        $this->paymentStatus = $paymentStatus;
        return $this;
    }

    public function withFromDate(string|null $fromDate): self
    {
        $this->fromDate = $fromDate;
        return $this;
    }

    public function withToDate(string|null $toDate): self
    {
        $this->toDate = $toDate;
        return $this;
    }

    public function withMinPrice(float|null $minPrice): self
    {
        $this->minPrice = $minPrice;
        return $this;
    }

    public function withMaxPrice(float|null $maxPrice): self
    {
        $this->maxPrice = $maxPrice;
        return $this;
    }

    public function withPerPage(int $perPage): self
    {
        $this->perPage = $perPage;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getOrderStatus(): ?string
    {
        return $this->orderStatus;
    }

    public function getPaymentStatus(): ?string
    {
        return $this->paymentStatus;
    }

    public function getFromDate(): ?string
    {
        return $this->fromDate;
    }

    public function getToDate(): ?string
    {
        return $this->toDate;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getMinPrice(): ?float
    {
        return $this->minPrice;
    }

    public function getMaxPrice(): ?float
    {
        return $this->maxPrice;
    }
}
