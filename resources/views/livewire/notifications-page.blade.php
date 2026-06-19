<div class="max-w-3xl mx-auto py-10 px-4">
    <h1 class="text-2xl font-semibold text-gray-800 dark:text-white mb-6">Notifications</h1>

    @if ($notifications->isEmpty())
        <div class="text-center text-gray-400 py-16">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto mb-3 opacity-40">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
            </svg>
            <p>You have no notifications yet.</p>
        </div>
    @else
        <ul class="bg-white dark:bg-gray-800 rounded-xl shadow divide-y divide-gray-100 dark:divide-gray-700">
            @foreach ($notifications as $notification)
                <li class="flex items-start gap-4 px-5 py-4 {{ is_null($notification->read_at) ? 'bg-blue-50 dark:bg-gray-700/50' : '' }}">
                    <div class="mt-0.5 shrink-0">
                        @if (is_null($notification->read_at))
                            <span class="block w-2 h-2 rounded-full bg-blue-500 mt-1"></span>
                        @else
                            <span class="block w-2 h-2 rounded-full bg-gray-300 dark:bg-gray-600 mt-1"></span>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <a href="{{ $notification->data['order_url'] }}" wire:navigate
                           class="text-sm font-medium text-gray-800 dark:text-gray-200 hover:underline">
                            {{ $notification->data['message'] }}
                        </a>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                            {{ $notification->created_at->diffForHumans() }}
                            @if (! is_null($notification->read_at))
                                &middot; Read {{ $notification->read_at->diffForHumans() }}
                            @endif
                        </p>
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
