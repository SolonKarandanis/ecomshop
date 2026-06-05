<?php

namespace App\Exceptions;

use App\Enums\HttpStatusCodeEnum;

class PaymentException extends \Exception
{
    public static function createStipeSession(): PaymentException
    {
        return new self('Failed to create Stripe session', HttpStatusCodeEnum::BAD_GATEWAY);
    }

    public static function retrieveStipeSession(): PaymentException
    {
        return new self('Failed to retrieve Stripe session', HttpStatusCodeEnum::BAD_GATEWAY);
    }

    public static function unsupportedPaymentMethod(string $paymentMethod): PaymentException
    {
        return new self("Unsupported payment method: $paymentMethod", HttpStatusCodeEnum::BAD_REQUEST);
    }
}
