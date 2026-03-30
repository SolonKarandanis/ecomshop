<div class="w-full max-w-340 py-10 px-4 sm:px-6 lg:px-8 mx-auto">
    <div class="flex h-full items-center">
        <main class="w-full max-w-md mx-auto p-6">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="p-4 sm:p-7">
                    <div class="text-center">
                        <h1 class="block text-2xl font-bold text-gray-800 dark:text-white">Sign in</h1>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Don't have an account yet?
                            <a class="text-blue-600 decoration-2 hover:underline font-medium dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600"
                               href="/register"
                               wire:navigate>
                                Sign up here
                            </a>
                        </p>
                    </div>

                    <hr class="my-5 border-slate-300">

                    <form wire:submit.prevent="performLogin()">
                        @if(session('error'))
                            <div class="bg-red-500 text-sm text-white rounded-lg p-4 mb-4" role="alert">
                                {{session('error')}}
                            </div>
                        @endif
                        <div class="grid gap-y-4">
                            <x-input name="email" label="Email address" type="email" wire:model="email" />
                            <x-input name="password" label="Password" type="password" wire:model="password" required>
                                <x-slot:labelAction>
                                    <a href="/forgot-password" class="text-sm text-blue-600 decoration-2 hover:underline font-medium" wire:navigate>
                                        Forgot password?
                                    </a>
                                </x-slot:labelAction>
                            </x-input>
                            <x-button type="submit" variant="primary" :full-width="true">
                                Sign in
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
