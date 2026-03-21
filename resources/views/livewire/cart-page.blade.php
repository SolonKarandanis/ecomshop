<div class="w-full max-w-340 py-10 px-4 sm:px-6 lg:px-8 mx-auto">
    <div class="container mx-auto px-4 ">
        <h1 class="text-2xl font-semibold mb-4 text-gray-700 dark:text-gray-400">Shopping Cart</h1>
        <div class="flex flex-col md:flex-row gap-4">
            <div class="md:w-3/4">
                <div class=" overflow-x-auto rounded-lg shadow-md p-6 mb-4 bg-white dark:bg-gray-200">
                    <table class="w-full">
                        <thead class="hidden md:table-header-group">
                        <tr>
                            <th class="text-left font-semibold px-4">Product</th>
                            <th class="text-left font-semibold px-4">Price</th>
                            <th class="text-left font-semibold px-4">Quantity</th>
                            <th class="text-left font-semibold px-4">Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($cart->cartItems as $cartItem)
                            <tr wire:key="cart-item-{{ $cartItem->id ?? $cartItem->id_from_cookie }}" class="block md:table-row border-b border-gray-200">
                                <td class="py-4 block md:table-cell">
                                    <span class="inline-block md:hidden font-bold">Product:</span>
                                    <div class="flex items-center">
                                        <img class="h-16 w-16 mr-4" src="{{$cartItem->product->getThumbnailImage()}}" alt="{{$cartItem->product->name}}">
                                        <span class="font-semibold text-sm">{{$cartItem->product->name}}</span>
                                    </div>
                                </td>
                                <td class="px-4 block md:table-cell">
                                    <span class="inline-block md:hidden font-bold">Price:</span>
                                    {{Number::currency($cartItem->unit_price,'eur')}}
                                </td>
                                <td class="px-4 block md:table-cell">
                                    <span class="inline-block md:hidden font-bold">Quantity:</span>
                                    <div class="flex items-center">
                                        <button wire:click="decreaseQuantity('{{$cartItem->id ?? $cartItem->id_from_cookie}}')" class="border rounded-md py-2 px-4 mr-2 cursor-pointer">
                                            -
                                        </button>
                                        <span class="text-center w-8">{{$cartItem->quantity}}</span>
                                        <button wire:click="increaseQuantity('{{$cartItem->id ?? $cartItem->id_from_cookie}}')" class="border rounded-md py-2 px-4 ml-2 cursor-pointer">
                                            +
                                        </button>
                                    </div>
                                </td>
                                <td class="px-4 block md:table-cell">
                                    <span class="inline-block md:hidden font-bold">Total:</span>
                                    {{Number::currency($cartItem->total_price,'eur')}}
                                </td>
                                <td class="px-4 block md:table-cell">
                                    <button wire:click="removeItem('{{$cartItem->id ?? $cartItem->id_from_cookie}}')"
                                        wire:loading.attr="disabled"
                                        wire:target="removeItem('{{$cartItem->id ?? $cartItem->id_from_cookie}}')"
                                        class="flex items-center bg-red-400 border-2 border-red-400 rounded-lg px-4 py-2 hover:bg-red-500  hover:border-red-500 cursor-pointer text-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.134-2.09-2.134H8.09a2.09 2.09 0 00-2.09 2.134v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                        <div wire:loading
                                             wire:target="removeItem({{$cartItem->id ?? $cartItem->id_from_cookie}})"
                                             class="animate-spin inline-block size-6 border-3 border-current border-t-transparent rounded-[999px] text-primary"
                                             role="status"
                                             aria-label="loading">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-4xl font-semibold text-shadow-slate-500">No items available in cart!</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="md:w-1/4">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold mb-4">Summary</h2>
                    <div class="flex justify-between mb-2">
                        <span>Subtotal</span>
                        <span>{{Number::currency($cart->total_price ?? 0,'eur')}}</span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span>Taxes</span>
                        <span>$1.99</span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span>Shipping</span>
                        <span>$0.00</span>
                    </div>
                    <hr class="my-2">
                    <div class="flex justify-between mb-2">
                        <span class="font-semibold">Total</span>
                        <span class="font-semibold">{{Number::currency($cart->total_price ?? 0,'eur')}}</span>
                    </div>
                    @if($cart->cartItems)
                        <button class="bg-blue-500 text-white hover:bg-blue-600 py-2 px-4 rounded-lg mt-4 w-full cursor-pointer">
                            Checkout
                        </button>
                        <button wire:click="clearCart()"
                            wire:loading.attr="disabled"
                            wire:target="clearCart"
                            class="flex items-center justify-center bg-red-600 border-2 border-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg mt-4 w-full cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.134-2.09-2.134H8.09a2.09 2.09 0 00-2.09 2.134v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                            <span class="ml-2"> Clear Cart</span>
                            <div wire:loading
                                 wire:target="clearCart()"
                                 class="animate-spin inline-block size-6 border-3 border-current border-t-transparent rounded-[999px] text-primary"
                                 role="status"
                                 aria-label="loading">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

