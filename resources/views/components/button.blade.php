@props([
    'type' => 'button',
    'variant' => 'primary',
    'fullWidth' => false,
    'loading' => false,
    'disabled' => false,
    'icon' => '',
    'wireTarget' => null,
])

@php
    $baseClasses = 'cursor-pointer inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent disabled:opacity-50 disabled:pointer-events-none dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600';

    $variantClasses = [
        'primary' => 'bg-blue-600 text-white hover:bg-blue-700',
        'success' => 'bg-green-500 text-white hover:bg-green-600',
        'danger' => 'bg-red-600 text-white hover:bg-red-700',
        'danger-outline' => 'bg-red-400 border-2 border-red-400 hover:bg-red-500 hover:border-red-500 text-white',
        'add-to-cart' => 'bg-blue-500 text-gray-50 hover:bg-blue-600 dark:bg-blue-500 dark:hover:bg-blue-700 dark:text-gray-200',
        'add-to-cart-gray' => 'text-gray-500 flex items-center space-x-2 dark:text-gray-400 hover:text-red-500 dark:hover:text-red-300',
    ];

    $widthClasses = $fullWidth ? 'w-full' : '';

    $paddingClasses = 'py-3 px-4';
    if ($variant === 'danger-outline') {
        $paddingClasses = 'py-2 px-4';
    }
    if ($variant === 'add-to-cart') {
        $paddingClasses = 'p-4';
    }

    $classes = "$baseClasses {$variantClasses[$variant]} $widthClasses $paddingClasses";

    $target = $wireTarget;
    if (!$target) {
        $clickAction = $attributes->get('wire:click');
        if ($clickAction) {
            preg_match('/^([a-zA-Z0-9_]+)/', $clickAction, $matches);
            if (!empty($matches)) {
                $target = $matches[1];
            }
        }
    }
@endphp

<button
    type="{{ $type }}"
    @if($loading)
        @if($target)
            wire:loading.attr="disabled" wire:target="{{ $target }}"
        @else
            wire:loading.attr="disabled"
        @endif
    @endif
    @if($disabled) disabled @endif
    {{ $attributes->merge(['class' => $classes]) }}
>
    @if($icon)
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
        </svg>
    @endif
    {{ $slot }}
    @if($loading)
        <div class="animate-spin inline-block size-6 border-3 border-current border-t-transparent rounded-[999px] text-primary" role="status" aria-label="loading"
            @if($target)
                wire:loading wire:target="{{ $target }}"
            @else
                wire:loading
            @endif
        >
            <span class="sr-only">Loading...</span>
        </div>
    @endif
</button>
