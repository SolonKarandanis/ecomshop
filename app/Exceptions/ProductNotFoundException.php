<?php

namespace App\Exceptions;

use App\Enums\HttpStatusCodeEnum;

class ProductNotFoundException extends \Exception
{
    public static function productNotFound(int $productId): ProductNotFoundException
    {
        return new self("Product with ID {$productId} not found.", HttpStatusCodeEnum::NOT_FOUND);
    }
}
