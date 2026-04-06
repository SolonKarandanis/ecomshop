<x-mail::message>
# Order Placed succsessfully!

Thank you for your order with number: {{$order->id}}. We will send you a confirmation email once your order has been shipped.

<x-mail::button :url="$url">
View Order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
