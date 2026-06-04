<?php

namespace App\Exceptions;

class ProductNotFoundException extends \Exception
{
    public static function productNotFound(int $productId): ProductNotFoundException
    {
        return new self("Product with ID {$productId} not found.", 404);
    }
}
