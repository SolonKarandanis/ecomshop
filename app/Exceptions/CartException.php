<?php

namespace App\Exceptions;

use App\Enums\HttpStatusCodeEnum;

class CartException extends \Exception
{
    public static function saveCart(): CartException
    {
        return new self('Failed to save cart', HttpStatusCodeEnum::BAD_REQUEST->value);
    }
    public static function updateItems(): CartException
    {
        return new self('Failed to update cart items', HttpStatusCodeEnum::BAD_REQUEST->value);
    }

    public static function deleteItems(): CartException
    {
        return new self('Failed to delete cart items', HttpStatusCodeEnum::BAD_REQUEST->value);
    }

    public static function clearCart(): CartException
    {
        return new self('Failed to clear cart', HttpStatusCodeEnum::BAD_REQUEST->value);
    }
}
