@use('App\Enums\OrderStatusEnum','OrderStatus')
@use('App\Enums\OrderPaymentStatusEnum','OrderPaymentStatus')
<div class="w-full max-w-340 py-10 px-4 sm:px-6 lg:px-8 mx-auto">
    <h1 class="text-4xl font-bold text-slate-500 dark:text-slate-400">{{__('order-page.title')}}</h1>
    <!-- Grid -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mt-5">
        <!-- Card -->
        <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-slate-900 dark:border-gray-800 overflow-hidden">
            <div class="p-4 md:p-5 flex gap-x-4">
                <div class="shrink-0 flex justify-center items-center size-11.5 bg-gray-100 rounded-lg dark:bg-gray-800">
                    <svg class="shrink-0 size-5 text-gray-600 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                </div>

                <div class="grow min-w-0">
                    <div class="flex items-center gap-x-2">
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 truncate">
                            {{__('order-page.customer')}}
                        </p>
                    </div>
                    <div class="mt-1 flex items-center gap-x-2 text-gray-800 dark:text-gray-200">
                        <div class="truncate">{{$order->address->getFullNameAttribute()}}</div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Card -->

        <!-- Card -->
        <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-slate-900 dark:border-gray-800 overflow-hidden">
            <div class="p-4 md:p-5 flex gap-x-4">
                <div class="shrink-0 flex justify-center items-center size-11.5 bg-gray-100 rounded-lg dark:bg-gray-800">
                    <svg class="shrink-0 size-5 text-gray-600 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 22h14" />
                        <path d="M5 2h14" />
                        <path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22" />
                        <path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2" />
                    </svg>
                </div>

                <div class="grow min-w-0">
                    <div class="flex items-center gap-x-2">
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 truncate">
                            {{__('order-page.order_date')}}
                        </p>
                    </div>
                    <div class="mt-1 flex items-center gap-x-2">
                        <h3 class="text-xl font-medium text-gray-800 dark:text-gray-200 truncate">
                            {{$order->created_at->format('d-m-Y')}}
                        </h3>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Card -->

        <!-- Card -->
        <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-slate-900 dark:border-gray-800 overflow-hidden">
            <div class="p-4 md:p-5 flex gap-x-4">
                <div class="shrink-0 flex justify-center items-center size-11.5 bg-gray-100 rounded-lg dark:bg-gray-800">
                    <svg class="shrink-0 size-5 text-gray-600 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 11V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h6" />
                        <path d="m12 12 4 10 1.7-4.3L22 16Z" />
                    </svg>
                </div>

                <div class="grow min-w-0">
                    <div class="flex items-center gap-x-2">
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 truncate">
                            {{__('order-page.order_status')}}
                        </p>
                    </div>
                    <div class="mt-1 flex items-center gap-x-2">
                        <span class="{{$this->getOrderStatusClass($order->order_status)}} py-1 px-3 rounded text-white shadow text-sm truncate">
                            {{OrderStatus::labels()[$order->order_status]}}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Card -->

        <!-- Card -->
        <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-slate-900 dark:border-gray-800 overflow-hidden">
            <div class="p-4 md:p-5 flex gap-x-4">
                <div class="shrink-0 flex justify-center items-center size-11.5 bg-gray-100 rounded-lg dark:bg-gray-800">
                    <svg class="shrink-0 size-5 text-gray-600 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12s2.545-5 7-5c4.454 0 7 5 7 5s-2.546 5-7 5c-4.455 0-7-5-7-5z" />
                        <path d="M12 13a1 1 0 1 0 0-2 1 1 0 0 0 0 2z" />
                        <path d="M21 17v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2" />
                        <path d="M21 7V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2" />
                    </svg>
                </div>

                <div class="grow min-w-0">
                    <div class="flex items-center gap-x-2">
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 truncate">
                            {{__('order-page.payment_status')}}
                        </p>
                    </div>
                    <div class="mt-1 flex items-center gap-x-2">
                        <span class="{{$this->getPaymentStatusClass($order->payment_status)}} py-1 px-3 rounded text-white shadow text-sm truncate">
                            {{OrderPaymentStatus::labels()[$order->payment_status]}}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Card -->
    </div>
    <!-- End Grid -->

    <div class="flex flex-col md:flex-row gap-4 mt-4">
        <div class="md:w-3/4">
            <div class="bg-white overflow-x-auto rounded-lg shadow-md p-6 mb-4">
                <table class="w-full">
                    <thead>
                    <tr>
                        <th class="text-left font-semibold">{{__('order-page.columns.product')}}</th>
                        <th class="text-left font-semibold">{{__('order-page.columns.price')}}</th>
                        <th class="text-left font-semibold">{{__('order-page.columns.quantity')}}</th>
                        <th class="text-left font-semibold">{{__('order-page.columns.total')}}</th>
                        @if($order->order_status === OrderStatus::Delivered->value)
                            <th class="text-left font-semibold">{{__('order-page.columns.review')}}</th>
                        @endif
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($order->items as $orderItem)
                        <tr wire:key="{{$orderItem->id}}">
                            <td class="py-6 max-w-3xs">
                                <div class="flex items-center">
                                    <img class="h-16 w-16 mr-4" src="{{$orderItem->product->getThumbnailImage()}}" alt="{{$orderItem->product->name}}">
                                    <span class="font-semibold text-sm">{{$orderItem->product->name}}</span>
                                </div>
                            </td>
                            <td class="py-4">{{Number::currency($orderItem->unit_amount ?? 0,'eur')}}</td>
                            <td class="py-4 ">
                                <span class="text-center w-8">{{$orderItem->quantity}}</span>
                            </td>
                            <td class="py-4">{{Number::currency($orderItem->total_amount ?? 0,'eur')}}</td>
                            @if($order->order_status === OrderStatus::Delivered->value)
                                <td class="py-4">
                                    <x-button
                                        :variant="$reviewingProductId === $orderItem->product_id ? 'danger-outline' : 'success'"
                                        wire:click="openReviewForm({{ $orderItem->product_id }})"
                                        :wire-target="'openReviewForm'"
                                    >
                                        {{ $reviewingProductId === $orderItem->product_id
                                            ? __('order-page.cancel_review')
                                            : __('order-page.review_this_product') }}
                                    </x-button>
                                </td>
                            @endif
                        </tr>
                        @if($order->order_status === OrderStatus::Delivered->value)
                            <tr wire:key="{{$orderItem->id}}-review-form">
                                <td colspan="5" class="pb-6">
                                    <div
                                        x-data="{ hoverRating: 0 }"
                                        x-show="$wire.reviewingProductId === {{ $orderItem->product_id }}"
                                        x-collapse.duration.300ms
                                        class="p-6 rounded-lg border border-gray-200 dark:border-gray-700"
                                        style="display: none;"
                                    >
                                        <h3 class="text-lg font-semibold dark:text-gray-400 mb-4">
                                            {{ $reviewId !== null ? __('product-details.edit_your_review') : __('product-details.write_a_review') }}
                                        </h3>

                                        <div class="mb-4">
                                            <label class="block text-sm mb-2 dark:text-white">{{ __('product-details.rating') }}</label>
                                            <div class="flex items-center gap-x-1">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <button type="button"
                                                            x-on:mouseenter="hoverRating = {{ $i }}"
                                                            x-on:mouseleave="hoverRating = 0"
                                                            x-on:click="$wire.rating = {{ $i }}">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor"
                                                             class="w-7 bi bi-star-fill cursor-pointer"
                                                             :class="(hoverRating || $wire.rating) >= {{ $i }} ? 'text-blue-500 dark:text-blue-400' : 'text-gray-300 dark:text-gray-600'"
                                                             viewBox="0 0 16 16">
                                                            <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z" />
                                                        </svg>
                                                    </button>
                                                @endfor
                                            </div>
                                            @error('rating')
                                            <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="mb-4">
                                            <label for="comment-{{ $orderItem->id }}" class="block text-sm mb-2 dark:text-white">{{ __('product-details.comment') }}</label>
                                            <textarea id="comment-{{ $orderItem->id }}" wire:model="comment" rows="4"
                                                      class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400 @error('comment') border-red-500 @enderror"></textarea>
                                            @error('comment')
                                            <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <x-button variant="primary" wire:click="submitReview" :wire-target="'submitReview'" :loading="true">
                                            {{ $reviewId !== null ? __('buttons.update_review') : __('buttons.submit_review') }}
                                        </x-button>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="bg-white overflow-x-auto rounded-lg shadow-md p-6 mb-4">
                <h1 class="font-3xl font-bold text-slate-500 mb-3">{{__('order-page.shipping.address')}}</h1>
                <div class="flex justify-between items-center">
                    <div>
                        <p>
                            {{$order->address->street_address}},{{$order->address->city}}, {{$order->address->country}}, {{$order->address->postal_code}}
                        </p>
                    </div>
                    <div>
                        <p class="font-semibold">{{__('order-page.shipping.phone')}}:</p>
                        <p>{{$order->address->phone}}</p>
                    </div>
                </div>
            </div>

        </div>
        <div class="md:w-1/4">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold mb-4">{{__('order-page.summary.title')}}</h2>
                <div class="flex justify-between mb-2">
                    <span>{{__('order-page.summary.sub_total')}}</span>
                    <span>{{Number::currency($order->grand_total ?? 0,'eur')}}</span>
                </div>
                <div class="flex justify-between mb-2">
                    <span>{{__('order-page.summary.taxes')}}</span>
                    <span>0</span>
                </div>
                <div class="flex justify-between mb-2">
                    <span>{{__('order-page.summary.shipping')}}</span>
                    <span>{{Number::currency($order->shipping_amount ?? 0,'eur')}}</span>
                </div>
                <hr class="my-2">
                <div class="flex justify-between mb-2">
                    <span class="font-semibold">{{__('order-page.summary.total')}}</span>
                    <span class="font-semibold">{{Number::currency($order->grand_total ?? 0,'eur')}}</span>
                </div>

            </div>
        </div>
    </div>
</div>
