<?php

namespace App\Exceptions;

use App\Enums\HttpStatusCodeEnum;

class ReviewException extends \Exception
{
    public static function notEligible(): ReviewException
    {
        return new self('You are not eligible to review this product', HttpStatusCodeEnum::BAD_REQUEST->value);
    }
}
