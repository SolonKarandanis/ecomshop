<?php

namespace App\Exceptions;

class PaymentException extends \Exception
{
    public static function createStipeSession(): PaymentException
    {
        return new self('Failed to create Stripe session', 400);
    }

    public static function retrieveStipeSession(): PaymentException
    {
        return new self('Failed to retrieve Stripe session', 400);
    }

    public static function unsupportedPaymentMethod(string $paymentMethod): PaymentException
    {
        return new self("Unsupported payment method: $paymentMethod", 400);
    }
}
