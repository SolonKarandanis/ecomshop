<?php

namespace App\Dtos;

class UpdateCartItemsDTO
{
    private int $cartItemId;
    private int $productId;
    private int $quantity;
    private array $attributes;


    public function getProductId(): int
    {
        return $this->productId;
    }

    public function setProductId(int $productId): void
    {
        $this->productId = $productId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    public function getCartItemId(): int
    {
        return $this->cartItemId;
    }

    public function setCartItemId(int $cartItemId): void
    {
        $this->cartItemId = $cartItemId;
    }


}
