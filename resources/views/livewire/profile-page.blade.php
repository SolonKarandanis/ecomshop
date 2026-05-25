<div class="w-full max-w-4xl py-10 px-4 sm:px-6 lg:px-8 mx-auto">
    <h1 class="text-4xl font-bold text-slate-500 dark:text-slate-400 mb-8">{{__('profile.title')}}</h1>

    {{-- Account Details --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700 mb-6">
        <div class="p-4 sm:p-7">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-1">{{__('profile.account_details.title')}}</h2>

            @if(auth()->user()->isAdmin())
                @php
                    $status = auth()->user()->status;
                    $badgeColor = match($status) {
                        \App\Enums\UserStatusEnum::ACTIVE   => 'bg-green-100 text-green-700 border-green-200',
                        \App\Enums\UserStatusEnum::INACTIVE => 'bg-red-100 text-red-700 border-red-200',
                    };
                @endphp
                <div class="mb-4">
                    <span class="text-sm text-gray-500 dark:text-gray-400 mr-2">{{__('profile.account_details.status')}}:</span>
                    <span class="inline-flex items-center py-0.5 px-2 rounded-full text-xs font-medium border {{ $badgeColor }}">
                        {{ \App\Enums\UserStatusEnum::labels()[$status->value] }}
                    </span>
                </div>
            @endif

            <form wire:submit.prevent="updateProfile">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm mb-2 dark:text-white">{{__('profile.account_details.name')}}</label>
                        <input id="name"
                               wire:model="name"
                               type="text"
                               class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400 @error('name') @enderror">
                        @error('name')
                            <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm mb-2 dark:text-white">{{__('profile.account_details.email')}}</label>
                        <input id="email"
                               wire:model="email"
                               type="email"
                               class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400 @error('email') @enderror">
                        @error('email')
                            <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <x-button type="submit" variant="primary" :loading="true" :wire-target="'updateProfile'">
                        {{__('profile.account_details.save')}}
                    </x-button>
                </div>
            </form>
        </div>
    </div>

    {{-- Change Password --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700 mb-6">
        <div class="p-4 sm:p-7">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">{{__('profile.change_password.title')}}</h2>

            <form wire:submit.prevent="changePassword">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="currentPassword" class="block text-sm mb-2 dark:text-white">{{__('profile.change_password.current_password')}}</label>
                        <input id="currentPassword"
                               wire:model="currentPassword"
                               type="password"
                               class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400 @error('currentPassword') @enderror">
                        @error('currentPassword')
                            <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="newPassword" class="block text-sm mb-2 dark:text-white">{{__('profile.change_password.new_password')}}</label>
                        <input id="newPassword"
                               wire:model="newPassword"
                               type="password"
                               class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400 @error('newPassword') @enderror">
                        @error('newPassword')
                            <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="newPasswordConfirmation" class="block text-sm mb-2 dark:text-white">{{__('profile.change_password.confirm_password')}}</label>
                        <input id="newPasswordConfirmation"
                               wire:model="newPasswordConfirmation"
                               type="password"
                               class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400 @error('newPasswordConfirmation') @enderror">
                        @error('newPasswordConfirmation')
                            <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <x-button type="submit" variant="primary" :loading="true" :wire-target="'changePassword'">
                        {{__('profile.change_password.save')}}
                    </x-button>
                </div>
            </form>
        </div>
    </div>

    {{-- Past Shipping Addresses --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <div class="p-4 sm:p-7">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">{{__('profile.addresses.title')}}</h2>

            @if($user->addresses->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">{{__('profile.addresses.empty')}}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase">{{__('profile.addresses.columns.name')}}</th>
                                <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase">{{__('profile.addresses.columns.address')}}</th>
                                <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase">{{__('profile.addresses.columns.order')}}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($user->addresses as $address)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200 whitespace-nowrap">
                                        {{ $address->full_name }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200">
                                        {{ $address->street_address }}, {{ $address->city }}, {{ $address->country }} {{ $address->postal_code }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                        Order #{{ $address->order_id }} &mdash; {{ $address->created_at->format('d M Y') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
