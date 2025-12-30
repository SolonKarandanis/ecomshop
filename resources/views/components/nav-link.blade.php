@props(['active','navigate'])

@php
    $classes = ($active ?? false)
                ? 'font-medium py-3 md:py-6  dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600 text-blue-600 dark:text-blue-500 dark:hover:text-blue-600'
                : 'font-medium py-3 md:py-6  dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600 text-gray-500 dark:text-gray-400 dark:hover:text-gray-500';
@endphp

<a {{$navigate??true?'wire:navigate':''}}  {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
