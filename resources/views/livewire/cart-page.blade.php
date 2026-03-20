<div class="w-full max-w-340 py-10 px-4 sm:px-6 lg:px-8 mx-auto">
    <div class="container mx-auto px-4 ">
        <h1 class="text-2xl font-semibold mb-4 text-gray-700 dark:text-gray-400">Shopping Cart</h1>
        <div class="flex flex-col md:flex-row gap-4">
            <div class="md:w-3/4">
                <div class=" overflow-x-auto rounded-lg shadow-md p-6 mb-4 bg-white dark:bg-gray-200">
                    <table class="w-full">
                        <thead>
                        <tr>
                            <th class="text-left font-semibold">Product</th>
                            <th class="text-left font-semibold">Price</th>
                            <th class="text-left font-semibold">Quantity</th>
                            <th class="text-left font-semibold">Total</th>
                            <th class="text-left font-semibold">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($cart->cartItems as $cartItem)
                            <tr wire:key="cart-item-{{ $cartItem->id ?? $cartItem->id_from_cookie }}">
                                <td class="py-4">
                                    <div class="flex items-center">
                                        <img class="h-16 w-16 mr-4" src="{{$cartItem->product->getThumbnailImage()}}" alt="{{$cartItem->product->name}}">
                                        <span class="font-semibold">{{$cartItem->product->name}}</span>
                                    </div>
                                </td>
                                <td class="py-4">{{Number::currency($cartItem->unit_price,'eur')}}</td>
                                <td class="py-4">
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
                                <td class="py-4">{{Number::currency($cartItem->total_price,'eur')}}</td>
                                <td>
                                    <button wire:click="removeItem('{{$cartItem->id ?? $cartItem->id_from_cookie}}')"
                                        class="bg-red-400 border-2 border-red-400 rounded-lg px-3 py-1 hover:bg-red-500  hover:border-red-500 cursor-pointer text-white">
                                        <span>Remove</span>
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
                            class="bg-red-600 border-2 border-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg mt-4 w-full cursor-pointer">
                            <span> Clear Cart</span>
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

