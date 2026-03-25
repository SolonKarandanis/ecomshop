<div class="w-full max-w-340 py-10 px-4 sm:px-6 lg:px-8 mx-auto">
    <div class="flex h-full items-center">
        <main class="w-full max-w-md mx-auto p-6">
            <div class="mt-7 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="p-4 sm:p-7">
                    <div class="text-center">
                        <h1 class="block text-2xl font-bold text-gray-800 dark:text-white">Reset password</h1>
                    </div>

                    <div class="mt-5">
                        <!-- Form -->
                        <form wire:submit.prevent="submit()">
                            @if(session('error'))
                                <div class="bg-red-500 text-sm text-white rounded-lg p-4 mb-4" role="alert">
                                    {{session('error')}}
                                </div>
                            @endif
                            <div class="grid gap-y-4">
                                <x-input name="password" label="Password" type="password" wire:model="password" required />
                                <x-input name="password_confirmation" label="Confirm Password" type="password" wire:model="password_confirmation" required />
                                <button type="submit" class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600">
                                    Save password
                                </button>
                            </div>
                        </form>
                        <!-- End Form -->
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
