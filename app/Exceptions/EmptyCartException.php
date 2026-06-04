<?php

namespace App\Exceptions;

class EmptyCartException extends \Exception
{
    public static function emptyCart(): EmptyCartException
    {
        return new self('Cart is empty',400);
    }
}
