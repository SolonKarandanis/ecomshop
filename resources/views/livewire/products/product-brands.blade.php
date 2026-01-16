<ul>
    @foreach($brands as $brand)
        <li class="mb-4" wire:key="{{$brand->id}}">
            <label for="{{$brand->slug}}" class="flex items-center text-gray-700 dark:text-white">
                <input type="checkbox"
                       wire:model.live="selected_brands"
                       id="{{$brand->slug}}"
                       value="{{$brand->id}}"
                       class="w-4 h-4 mr-2">
                <span class="text-lg text-gray-700 dark:text-white">{{$brand->name}}</span>
            </label>
        </li>
    @endforeach
</ul>
