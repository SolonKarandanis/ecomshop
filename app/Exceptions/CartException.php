<?php

namespace App\Exceptions;

class CartException extends \Exception
{
    public static function updateItems(): CartException
    {
        return new self('Failed to update cart items', 400);
    }

    public static function deleteItems(): CartException
    {
        return new self('Failed to delete cart items', 400);
    }

    public static function clearCart(): CartException
    {
        return new self('Failed to clear cart', 400);
    }
}
