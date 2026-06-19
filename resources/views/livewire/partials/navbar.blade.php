<header class="flex z-50 sticky top-0 flex-wrap md:justify-start md:flex-nowrap w-full bg-white text-sm py-3 md:py-0 dark:bg-gray-800 shadow-md">
    <nav class="max-w-[85rem] w-full mx-auto px-4 md:px-6 lg:px-8" aria-label="Global">
        <div class="relative md:flex md:items-center md:justify-between">
            <div class="flex items-center justify-between">
                <a class="flex-none text-xl font-semibold dark:text-white dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600" href="/" aria-label="Brand">{{ config('app.name', 'Laravel') }}</a>
                <div class="md:hidden">
                    <button type="button" class="hs-collapse-toggle flex justify-center items-center w-9 h-9 text-sm font-semibold rounded-lg border border-gray-200 text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:text-white dark:border-gray-700 dark:hover:bg-gray-700 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600" data-hs-collapse="#navbar-collapse-with-animation" aria-controls="navbar-collapse-with-animation" aria-label="Toggle navigation">
                        <svg class="hs-collapse-open:hidden flex-shrink-0 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" x2="21" y1="6" y2="6" />
                            <line x1="3" x2="21" y1="12" y2="12" />
                            <line x1="3" x2="21" y1="18" y2="18" />
                        </svg>
                        <svg class="hs-collapse-open:block hidden flex-shrink-0 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div id="navbar-collapse-with-animation" class="hs-collapse hidden transition-all duration-300 basis-full grow md:block">
                <div class="overflow-visible max-h-[75vh] md:max-h-none [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-track]:bg-slate-700 dark:[&::-webkit-scrollbar-thumb]:bg-slate-500">
                    <div class="flex flex-col gap-x-0 mt-5 divide-y divide-dashed divide-gray-200 md:flex-row md:items-center md:justify-end md:gap-x-7 md:mt-0 md:ps-7 md:divide-y-0 md:divide-solid dark:divide-gray-700">

                        <x-nav-link href="{{route('home')}}" :active="request()->routeIs('home')">{{__('navbar.home')}}</x-nav-link>
                        <x-nav-link href="{{route('categories')}}" :active="request()->routeIs('categories')">{{__('navbar.categories')}}</x-nav-link>
                        <x-nav-link href="{{route('products')}}" :active="request()->routeIs('products')">{{__('navbar.products')}}</x-nav-link>
                        <x-nav-link href="{{route('cart')}}" :active="request()->routeIs('cart')" class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="shrink-0 w-5 h-5 mr-1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                            <span class="mr-1">{{__('navbar.cart')}}</span>
                            <span class="py-0.5 px-1.5 rounded-full text-xs font-medium bg-blue-50 border border-blue-200 text-blue-600">{{$this->total_cart_items}}</span>
                        </x-nav-link>

                        @guest
                        <div class="pt-3 md:pt-0">
                            <a class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600" href="/login">
                                <svg class="flex-shrink-0 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                                {{__('navbar.buttons.log_in')}}
                            </a>
                        </div>
                        @endguest

                        @auth
                        {{-- Notification bell --}}
                        <div class="relative md:py-4">
                            <button
                                wire:click="openDropdown"
                                type="button"
                                class="relative flex items-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                aria-label="Notifications"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                                </svg>
                                @if ($unread_notifications_count > 0)
                                    <span class="absolute -top-1 -right-1 flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-red-500 rounded-full">
                                        {{ $unread_notifications_count > 9 ? '9+' : $unread_notifications_count }}
                                    </span>
                                @endif
                            </button>

                            @if ($dropdown_open)
                            <div
                                class="absolute right-0 top-full mt-2 w-96 bg-white border border-gray-200 rounded-lg shadow-lg z-70 dark:bg-gray-800 dark:border-gray-700"
                                wire:click.outside="closeDropdown"
                            >
                                <div class="p-3 border-b border-gray-200 dark:border-gray-700">
                                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide dark:text-gray-400">Notifications</span>
                                </div>

                                @if (count($dropdown_notifications) > 0)
                                    <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                                        @foreach ($dropdown_notifications as $notification)
                                            <li>
                                                <a href="{{ $notification['order_url'] }}" wire:navigate
                                                   class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm text-gray-700 dark:text-gray-300">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mt-0.5 shrink-0 text-blue-500">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                                                    </svg>
                                                    {{ $notification['message'] }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="px-4 py-6 text-sm text-center text-gray-400 dark:text-gray-500">No unread notifications</p>
                                @endif

                                <div class="p-2 border-t border-gray-200 dark:border-gray-700 text-center">
                                    <a href="{{ route('notifications') }}" wire:navigate class="text-xs text-blue-600 hover:underline dark:text-blue-400">See all notifications</a>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="hs-dropdown [--strategy:static] md:[--strategy:absolute] [--adaptive:none] md:[--trigger:hover] md:py-4">
                            <button type="button" class="flex items-center w-full text-gray-500 hover:text-gray-400 font-medium dark:text-gray-400 dark:hover:text-gray-500">
                                {{auth()->user()->name}}
                                <svg class="ms-2 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m6 9 6 6 6-6" />
                                </svg>
                            </button>

                            <div class="hs-dropdown-menu transition-[opacity,margin] duration-[0.1ms] md:duration-[150ms] hs-dropdown-open:opacity-100 opacity-0 md:w-48 hidden z-60 bg-white md:shadow-md rounded-lg p-2 dark:bg-gray-800 md:dark:border dark:border-gray-700 dark:divide-gray-700 before:absolute top-full md:border before:-top-5 before:start-0 before:w-full before:h-5 md:right-0">
                                <a href="{{ route('my-orders') }}" wire:navigate
                                   class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:ring-2
                                        focus:ring-blue-500 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-300 dark:focus:outline-none
                                        dark:focus:ring-1 dark:focus:ring-gray-600" >
                                    {{__('navbar.my_orders')}}
                                </a>

                                <a href="{{ route('notifications') }}" wire:navigate
                                   class="flex items-center justify-between gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:ring-2
                                        focus:ring-blue-500 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-300 dark:focus:outline-none
                                        dark:focus:ring-1 dark:focus:ring-gray-600">
                                    Notifications
                                    @if ($unread_notifications_count > 0)
                                        <span class="py-0.5 px-1.5 rounded-full text-[10px] font-bold bg-red-500 text-white">
                                            {{ $unread_notifications_count > 9 ? '9+' : $unread_notifications_count }}
                                        </span>
                                    @endif
                                </a>

                                <a href="{{ route('profile') }}" wire:navigate
                                   class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:ring-2
                                        focus:ring-blue-500 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-300 dark:focus:outline-none dark:focus:ring-1
                                        dark:focus:ring-gray-600">
                                    {{__('navbar.my_account')}}
                                </a>
                                <a class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:ring-2
                                focus:ring-blue-500 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-300 dark:focus:outline-none
                                dark:focus:ring-1 dark:focus:ring-gray-600" href="/logout">
                                    {{__('navbar.logout')}}
                                </a>
                            </div>
                        </div>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>
