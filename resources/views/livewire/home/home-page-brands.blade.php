<div class="justify-center max-w-6xl px-4 py-4 mx-auto lg:py-0">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4 md:grid-cols-2">
        @foreach($brands as $brand)
            <div class="bg-white rounded-lg shadow-md dark:bg-gray-800" wire:key="{{$brand->id}}">
                <a href="/products?brands[0]={{$brand->id}}" class="">
                    <img src="{{$brand->getSmallImage()}}" alt="{{$brand->name}}" class="object-cover w-full h-64 rounded-t-lg">
                </a>
                <div class="p-5 text-center">
                    <a href="#" class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-300">
                        {{$brand->name}}
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
