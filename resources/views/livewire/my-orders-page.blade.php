@use('App\Enums\OrderStatusEnum','OrderStatus')
@use('App\Enums\OrderPaymentStatusEnum','OrderPaymentStatus')
<div class="w-full max-w-7xl py-10 px-4 sm:px-6 lg:px-8 mx-auto">
    <h1 class="text-4xl font-bold text-slate-500 dark:text-slate-400">{{__('my-orders.title')}}</h1>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700 mt-4">
        <div class="p-4 sm:p-7">
            <h2 class="block text-xl font-bold text-gray-800 dark:text-white mb-4">{{__('my-orders.search.title')}}</h2>
            <form wire:submit.prevent="search">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <div>
                        <label for="orderStatus" class="block text-sm mb-2 dark:text-white">{{__('my-orders.search.fields.order_status')}}</label>
                        <select wire:model="orderStatus" id="orderStatus" class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400 dark:focus:ring-gray-600">
                            <option value="">All</option>
                            @foreach(OrderStatus::labels() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="paymentStatus" class="block text-sm mb-2 dark:text-white">{{__('my-orders.search.fields.payment_status')}}</label>
                        <select wire:model="paymentStatus" id="paymentStatus" class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400 dark:focus:ring-gray-600">
                            <option value="">All</option>
                            @foreach(OrderPaymentStatus::labels() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <x-input name="fromDate" :label="__('my-orders.search.fields.from_date')" type="date" wire:model.live="fromDate" :max="$toDate" />
                    <x-input name="toDate" :label="__('my-orders.search.fields.to_date')" type="date" wire:model.live="toDate" :min="$fromDate" />
                    <x-input name="minPrice" :label="__('my-orders.search.fields.min_amount')" type="number" step="0.01" wire:model.live="minPrice" :max="$maxPrice" />
                    <x-input name="maxPrice" :label="__('my-orders.search.fields.max_amount')" type="number" step="0.01" wire:model.live="maxPrice" :min="$minPrice" />
                </div>

                <div class="mt-4 flex justify-between gap-2">
                    <x-button type="submit" variant="primary" :loading="true" :wire-target="'search'">
                        {{__('my-orders.buttons.search')}}
                    </x-button>
                    <x-button type="button" variant="danger" wire:click="resetSearch" :loading="true" :wire-target="'resetSearch'">
                        {{__('my-orders.buttons.reset')}}
                    </x-button>
                </div>
            </form>
        </div>
    </div>

    <div class="flex flex-col bg-white p-5 rounded mt-4 shadow-lg">
        <div class="-m-1.5 overflow-x-auto">
            <div class="p-1.5 min-w-full inline-block align-middle">
                <div class="overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                        <tr>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                {{__('my-orders.columns.order')}}
                            </th>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                {{__('my-orders.columns.date')}}
                            </th>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                {{__('my-orders.columns.order_status')}}
                            </th>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                {{__('my-orders.columns.payment_status')}}
                            </th>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                {{__('my-orders.columns.amount')}}
                            </th>
                            <th scope="col" class="px-8 py-3 text-end text-xs font-medium text-gray-500 uppercase">
                                {{__('my-orders.columns.action')}}
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr wire:key="{{$order->id}}" class="odd:bg-white even:bg-gray-100 dark:odd:bg-slate-900 dark:even:bg-slate-800">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-gray-200">
                                        {{$order->id}}
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                        {{$order->created_at->format('d M, Y')}}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                        <span class="{{$this->getOrderStatusClass($order->order_status)}} py-1 px-3 rounded text-white shadow">
                                             {{OrderStatus::labels()[$order->order_status]}}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                        <span class="{{$this->getPaymentStatusClass($order->payment_status)}} py-1 px-3 rounded text-white shadow">
                                            {{OrderPaymentStatus::labels()[$order->payment_status]}}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                        {{Number::currency($order->grand_total ?? 0,'eur')}}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                                        <a href="{{ route('my-orders.detail',$order->id) }}" wire:navigate class="bg-slate-600 text-white py-2
                                        px-4 rounded-md hover:bg-slate-500">
                                            {{__('my-orders.buttons.view')}}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="flex justify-between mt-6">
                        <x-button
                            variant="primary"
                            wire:click="exportOrders()"
                            :loading="true"
                            icon="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"
                            class="mt-4 py-2 px-4"
                        >
                            {{__('my-orders.buttons.export')}}
                        </x-button>
                        {{$orders->links('vendor.pagination.livewire-tailwind', data: ['scrollTo' => false])}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
