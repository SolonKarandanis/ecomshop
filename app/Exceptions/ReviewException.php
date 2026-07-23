<?php

namespace App\Exceptions;

use App\Enums\HttpStatusCodeEnum;

class ReviewException extends \Exception
{
    public static function notEligible(): ReviewException
    {
        return new self(__('messages.submit_review.not_eligible'), HttpStatusCodeEnum::BAD_REQUEST->value);
    }

    public static function createReview(): ReviewException
    {
        return new self(__('messages.submit_review.creation_error'), HttpStatusCodeEnum::INTERNAL_SERVER_ERROR->value);
    }

    public static function updateReview(): ReviewException
    {
        return new self(__('messages.submit_review.update_error'), HttpStatusCodeEnum::INTERNAL_SERVER_ERROR->value);
    }
}
