<?php

namespace App\Exceptions;

class OrderException extends \Exception
{

    public static function createOrder(): OrderException
    {
        return new self(__('messages.order_exceptions.create_order'),400);
    }

    public static function checkout(): OrderException
    {
        return new self(__('messages.order_exceptions.checkout'),400);
    }

    public static function stripeProcessing(): OrderException
    {
        return new self(__('messages.order_exceptions.stripe_processing'),400);
    }

}
