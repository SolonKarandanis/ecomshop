@props([
    'name',
    'label',
    'type' => 'text'
])

<div>
    <div class="flex justify-between items-center">
        <label for="{{ $name }}" class="block text-sm mb-2 dark:text-white">{{ $label }}</label>
        {{-- This is the slot for additional actions like 'Forgot Password?' --}}
        @if(isset($labelAction))
            {{ $labelAction }}
        @endif
    </div>
    <div class="relative">
        <input
            type="{{ $type }}"
            id="{{ $name }}"
            name="{{ $name }}"
            {{ $attributes->merge(['class' => 'py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400 dark:focus:ring-gray-600']) }}
            aria-describedby="{{ $name }}-error"
        >
        @error($name)
            <div class="absolute inset-y-0 end-0 flex items-center pointer-events-none pe-3">
                <svg class="h-5 w-5 text-red-500" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z" />
                </svg>
            </div>
        @enderror
    </div>
    @error($name)
        <p class="text-xs text-red-600 mt-2" id="{{ $name }}-error">{{ $message }}</p>
    @enderror
</div>
