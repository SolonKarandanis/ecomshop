<ul>
    @foreach($categories as $category)
        <li class="mb-4" wire:key="{{$category->id}}">
            <label for="{{$category->slug}}" class="flex items-center text-gray-700 dark:text-white">
                <input type="checkbox"
                       wire:model.live="selected_categories"
                       id="{{$category->slug}}"
                       value="{{$category->id}}"
                       class="w-4 h-4 mr-2">
                <span class="text-lg">{{$category->name}}</span>
            </label>
        </li>
    @endforeach
</ul>
