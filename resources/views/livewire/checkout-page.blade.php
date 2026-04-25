@use('App\Enums\PaymentMethodEnum','PaymentMethod')
<div class="w-full max-w-340 py-10 px-4 sm:px-6 lg:px-8 mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">
        {{__('checkout.title')}}
    </h1>
    <form wire:submit.prevent="save()" class="grid grid-cols-12 gap-4">
        <div class="md:col-span-12 lg:col-span-8 col-span-12">
            <!-- Card -->
            <div class="bg-white rounded-xl shadow p-4 sm:p-7 dark:bg-slate-900">
                <!-- Shipping Address -->
                <div class="mb-6">
                    <h2 class="text-xl font-bold underline text-gray-700 dark:text-white mb-2">
                        {{__('checkout.shipping.title')}}
                    </h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 dark:text-white mb-1" for="firstName">
                                {{__('checkout.shipping.first_name')}}
                            </label>
                            <input name="firstName"
                                   wire:model="firstName"
                                   id="firstName"
                                   type="text"
                                   class="w-full rounded-lg border py-2 px-3 dark:bg-gray-700 dark:text-white @error('firstName') border-red-500 @enderror" >
                            @error('firstName')
                                <p class="text-xs text-red-600 mt-2" id="firstName-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white mb-1" for="lastName">
                                {{__('checkout.shipping.last_name')}}
                            </label>
                            <input name="lastName"
                                   wire:model="lastName"
                                   id="lastName"
                                   type="text"
                                   class="w-full rounded-lg border py-2 px-3 dark:bg-gray-700 dark:text-white  @error('lastName') border-red-500 @enderror" >
                            @error('lastName')
                                <p class="text-xs text-red-600 mt-2" id="lastName-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-gray-700 dark:text-white mb-1" for="phone">
                            {{__('checkout.shipping.phone')}}
                        </label>
                        <input name="phone"
                               wire:model="phone"
                               id="phone"
                               type="text"
                               class="w-full rounded-lg border py-2 px-3 dark:bg-gray-700 dark:text-white  @error('phone') border-red-500 @enderror" >
                        @error('phone')
                            <p class="text-xs text-red-600 mt-2" id="phone-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mt-4">
                        <label class="block text-gray-700 dark:text-white mb-1" for="address">
                            {{__('checkout.shipping.address')}}
                        </label>
                        <input name="address"
                               wire:model="address"
                               id="address"
                               type="text"
                               class="w-full rounded-lg border py-2 px-3 dark:bg-gray-700 dark:text-white  @error('address') border-red-500 @enderror" >
                        @error('address')
                            <p class="text-xs text-red-600 mt-2" id="address-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mt-4">
                        <label class="block text-gray-700 dark:text-white mb-1" for="city">
                            {{__('checkout.shipping.city')}}
                        </label>
                        <input name="city"
                               wire:model="city"
                               id="city"
                               type="text"
                               class="w-full rounded-lg border py-2 px-3 dark:bg-gray-700 dark:text-white @error('city') border-red-500 @enderror" >
                        @error('city')
                            <p class="text-xs text-red-600 mt-2" id="city-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-gray-700 dark:text-white mb-1" for="country">
                                {{__('checkout.shipping.country')}}
                            </label>
                            <input name="country"
                                   wire:model="country"
                                   id="country"
                                   type="text"
                                   class="w-full rounded-lg border py-2 px-3 dark:bg-gray-700 dark:text-white @error('country') border-red-500 @enderror" >
                            @error('country')
                                <p class="text-xs text-red-600 mt-2" id="state-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white mb-1" for="zipCode">
                                {{__('checkout.shipping.zip')}}
                            </label>
                            <input name="zipCode"
                                   wire:model="zipCode"
                                   id="zipCode"
                                   type="text"
                                   class="w-full rounded-lg border py-2 px-3 dark:bg-gray-700 dark:text-white @error('zipCode') border-red-500 @enderror" >
                            @error('zipCode')
                                <p class="text-xs text-red-600 mt-2" id="zipCode-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="text-lg font-semibold mb-4 text-gray-700 dark:text-white">
                    {{__('checkout.shipping.select_shipping_method')}}
                </div>
                <ul class="grid w-full gap-6 md:grid-cols-2 @error('paymentMethod') border-red-500 @enderror">
                    @foreach(PaymentMethod::cases() as $method)
                    <li>
                        <input name="paymentMethod"
                               wire:model="paymentMethod"
                               id="{{$method->value}}"
                               type="radio"
                               class="hidden peer"
                               value="{{$method->value}}">
                        <label class="inline-flex items-center justify-between w-full p-5 text-gray-500 bg-white border border-gray-200 rounded-lg cursor-pointer dark:hover:text-gray-300 dark:border-gray-700 dark:peer-checked:text-blue-500 peer-checked:border-blue-600 peer-checked:text-blue-600 hover:text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:bg-gray-800 dark:hover:bg-gray-700" for="{{$method->value}}">
                            <div class="block">
                                <div class="w-full text-lg font-semibold">
                                    {{PaymentMethod::labels()[$method->value]}}
                                </div>
                            </div>
                            <svg aria-hidden="true" class="w-5 h-5 ms-3 rtl:rotate-180" fill="none" viewbox="0 0 14 10" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                                </path>
                            </svg>
                        </label>
                    </li>
                    @endforeach
                </ul>
                @error('paymentMethod')
                    <p class="text-xs text-red-600 mt-2" id="paymentMethod-error">{{ $message }}</p>
                @enderror
            </div>
            <!-- End Card -->
        </div>
        <div class="md:col-span-12 lg:col-span-4 col-span-12">
            <div class="bg-white rounded-xl shadow p-4 sm:p-7 dark:bg-slate-900">
                <div class="text-xl font-bold underline text-gray-700 dark:text-white mb-2">
                    {{__('checkout.order_summary.title')}}
                </div>
                <div class="flex justify-between mb-2 font-bold text-gray-700 dark:text-white">
					<span>
						{{__('checkout.order_summary.sub_total')}}
					</span>
                    <span>
						{{Number::currency($cart->total_price ?? 0,'eur')}}
					</span>
                </div>
                <div class="flex justify-between mb-2 font-bold text-gray-700 dark:text-white">
					<span>
						{{__('checkout.order_summary.taxes')}}
					</span>
                    <span>
						0.00
					</span>
                </div>
                <div class="flex justify-between mb-2 font-bold text-gray-700 dark:text-white">
					<span>
						{{__('checkout.order_summary.shipping')}}
					</span>
                    <span>
						0.00
					</span>
                </div>
                <hr class="bg-slate-400 my-4 h-1 rounded">
                <div class="flex justify-between mb-2 font-bold text-gray-700 dark:text-white">
					<span>
						{{__('checkout.order_summary.total')}}
					</span>
                    <span>
						{{Number::currency($cart->total_price ?? 0,'eur')}}
					</span>
                </div>
                </hr>
            </div>
            <x-button type="submit" variant="success" :full-width="true" class="mt-4 p-3 text-lg" :loading="true" wire-target="save">
                {{__('checkout.buttons.place_order')}}
            </x-button>
            <div class="bg-white mt-4 rounded-xl shadow p-4 sm:p-7 dark:bg-slate-900">
                <div class="text-xl font-bold underline text-gray-700 dark:text-white mb-2">
                    {{__('checkout.cart_summary.title')}}
                </div>
                <ul class="divide-y divide-gray-200 dark:divide-gray-700" role="list">
                    @foreach($cart->cartItems as $cartItem)
                        <li wire:key="cart-item-{{ $cartItem->id }}" class="py-3 sm:py-4">
                            <div class="flex items-center">
                                <div class="shrink-0">
                                    <img class="h-16 w-16 mr-4" src="{{$cartItem->product->getThumbnailImage()}}" alt="{{$cartItem->product->name}}">
                                </div>
                                <div class="flex-1 min-w-0 ms-4">
                                    <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                                        {{$cartItem->product->name}}
                                    </p>
                                    <p class="text-sm text-gray-500 truncate dark:text-gray-400">
                                        {{__('checkout.cart_summary.quantity')}}: {{$cartItem->quantity}}
                                    </p>
                                </div>
                                <div class="inline-flex items-center text-base font-semibold text-gray-900 dark:text-white">
                                    {{Number::currency($cartItem->total_price,'eur')}}
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </form>
</div>
