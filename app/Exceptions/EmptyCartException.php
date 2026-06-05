<?php

namespace App\Exceptions;

use App\Enums\HttpStatusCodeEnum;

class EmptyCartException extends \Exception
{
    public static function emptyCart(): EmptyCartException
    {
        return new self('Cart is empty',HttpStatusCodeEnum::BAD_REQUEST);
    }
}
