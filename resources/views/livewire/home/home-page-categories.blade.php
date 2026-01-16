<div class="max-w-340 px-4 sm:px-6 lg:px-8 mx-auto">
    <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-6">

        @foreach($categories as $category)
            <a wire:key="{{$category->id}}"
               class="group flex flex-col bg-white border shadow-sm rounded-xl hover:shadow-md transition
                    dark:bg-slate-900 dark:border-gray-800 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600"
               href="/products?categories[0]={{$category->id}}">
                <div class="p-4 md:p-5">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <img class="h-9.5 w-9.5 rounded-full" src="{{$category->getThumbnailImage()}}" alt="{{$category->name}}">
                            <div class="ms-3">
                                <h3 class="group-hover:text-blue-600 font-semibold text-gray-800 dark:group-hover:text-gray-400 dark:text-gray-200">
                                    {{$category->name}}
                                </h3>
                            </div>
                        </div>
                        <div class="ps-3">
                            <svg class="shrink-0 w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m9 18 6-6-6-6" />
                            </svg>
                        </div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>
