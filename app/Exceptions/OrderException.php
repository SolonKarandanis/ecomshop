<?php

namespace App\Exceptions;

class OrderException extends \Exception
{

    public static function createOrder(): OrderException
    {
        return new self('Failed to create new order',400);
    }

    public static function checkout(): OrderException
    {
        return new self('Something went wrong during checkout!',400);
    }

    public static function stripeProcessing(): OrderException
    {
        return new self('Something went wrong during stripe order processing!',400);
    }

}
