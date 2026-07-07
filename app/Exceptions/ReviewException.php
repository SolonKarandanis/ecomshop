<?php

namespace App\Exceptions;

use App\Enums\HttpStatusCodeEnum;

class ReviewException extends \Exception
{
    public static function notEligible(): ReviewException
    {
        return new self(__('messages.submit_review.not_eligible'), HttpStatusCodeEnum::BAD_REQUEST->value);
    }
}
