@props([
    'columnName' => null,
    'sortColumn' => null,
    'sortDirection' => null,
])

<th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
    @if($columnName)
        <button wire:click="sort('{{ $columnName }}')" class="flex items-center gap-1 hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer">
            {{ $slot }}
            @if($sortColumn === $columnName)
                @if($sortDirection === 'asc')
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l5 5a1 1 0 01-1.414 1.414L10 5.414 5.707 9.707A1 1 0 014.293 8.293l5-5A1 1 0 0110 3z" clip-rule="evenodd"/></svg>
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 17a1 1 0 01-.707-.293l-5-5a1 1 0 011.414-1.414L10 14.586l4.293-4.293a1 1 0 011.414 1.414l-5 5A1 1 0 0110 17z" clip-rule="evenodd"/></svg>
                @endif
            @else
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 opacity-30" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707A1 1 0 016.293 6.293l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            @endif
        </button>
    @else
        {{ $slot }}
    @endif
</th>
