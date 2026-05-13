<?php

namespace App\Http\Requests;

use App\Enums\OrderPaymentStatusEnum;
use App\Enums\OrderStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class OrderSearchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'orderStatus' => ['nullable', new Enum(OrderStatusEnum::class)],
            'paymentStatus' => ['nullable', new Enum(OrderPaymentStatusEnum::class)],
            'fromDate' => ['nullable', 'date', 'before_or_equal:toDate'],
            'toDate' => ['nullable', 'date', 'after_or_equal:fromDate'],
            'minPrice' => ['nullable', 'numeric', 'min:0', 'lte:maxPrice'],
            'maxPrice' => ['nullable', 'numeric', 'min:0', 'gte:minPrice'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
