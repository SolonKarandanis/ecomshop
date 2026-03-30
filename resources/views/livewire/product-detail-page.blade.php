@php
    use Illuminate\Support\Number;

    $attributes = [];

    if ($hasColorAttribute) {
        $colorAttributeValues = $product->colorAttributeValues;
        $colorAttributeId = $colorAttributeValues->first()->attribute->id;
        $colorOptions = $colorAttributeValues->map(function ($item) {
            return ['id' => $item->attributeOption->id, 'name' => $item->attributeOption->option_name];
        })->unique('id')->values();
        $attributes[] = [
            'id' => $colorAttributeId,
            'name' => 'color',
            'label' => 'Color',
            'values' => $colorAttributeValues->values(),
            'options' => $colorOptions,
            'initial' => $colorOptions->isNotEmpty() ? $colorOptions->first()['id'] : null,
        ];
    }

    if ($hasPanelTypeAttribute) {
        $panelTypeAttributeValues = $product->panelTypeAttributeValues;
        $panelTypeAttributeId = $panelTypeAttributeValues->first()->attribute->id;
        $panelOptions = $panelTypeAttributeValues->map(function ($item) {
            return ['id' => $item->attributeOption->id, 'name' => $item->attributeOption->option_name];
        })->unique('id')->values();
        $attributes[] = [
            'id' => $panelTypeAttributeId,
            'name' => 'panel',
            'label' => 'Panel Type',
            'values' => $panelTypeAttributeValues->values(),
            'options' => $panelOptions,
            'initial' => $panelOptions->isNotEmpty() ? $panelOptions->first()['id'] : null,
        ];
    }

    if ($hasHardDriveAttribute) {
        $hardDriveAttributeValues = $product->hardDriveAttributeValues;
        $hardDriveAttributeId = $hardDriveAttributeValues->first()->attribute->id;
        $hardDriveOptions = $hardDriveAttributeValues->map(function ($item) {
            return ['id' => $item->attributeOption->id, 'name' => $item->attributeOption->option_name];
        })->unique('id')->values();
        $attributes[] = [
            'id' => $hardDriveAttributeId,
            'name' => 'hard_drive',
            'label' => 'Hard Drive',
            'values' => $hardDriveAttributeValues->values(),
            'options' => $hardDriveOptions,
            'initial' => $hardDriveOptions->isNotEmpty() ? $hardDriveOptions->first()['id'] : null,
        ];
    }

    if ($hasKeyboardAttribute) {
        $keyboardAttributeValues = $product->keyboardAttributeValues;
        $keyboardAttributeId = $keyboardAttributeValues->first()->attribute->id;
        $keyboardOptions = $keyboardAttributeValues->map(function ($item) {
            return ['id' => $item->attributeOption->id, 'name' => $item->attributeOption->option_name];
        })->unique('id')->values();
        $attributes[] = [
            'id' => $keyboardAttributeId,
            'name' => 'keyboard',
            'label' => 'Keyboard',
            'values' => $keyboardAttributeValues->values(),
            'options' => $keyboardOptions,
            'initial' => $keyboardOptions->isNotEmpty() ? $keyboardOptions->first()['id'] : null,
        ];
    }

    if ($hasRamAttribute) {
        $ramAttributeValues = $product->ramAttributeValues;
        $ramAttributeId = $ramAttributeValues->first()->attribute->id;
        $ramOptions = $ramAttributeValues->map(function ($item) {
            return ['id' => $item->attributeOption->id, 'name' => $item->attributeOption->option_name];
        })->unique('id')->values();
        $attributes[] = [
            'id' => $ramAttributeId,
            'name' => 'ram',
            'label' => 'RAM',
            'values' => $ramAttributeValues->values(),
            'options' => $ramOptions,
            'initial' => $ramOptions->isNotEmpty() ? $ramOptions->first()['id'] : null,
        ];
    }

    if ($hasGpuAttribute) {
        $gpuAttributeValues = $product->gpuAttributeValues;
        $gpuAttributeId = $gpuAttributeValues->first()->attribute->id;
        $gpuOptions = $gpuAttributeValues->map(function ($item) {
            return ['id' => $item->attributeOption->id, 'name' => $item->attributeOption->option_name];
        })->unique('id')->values();
        $attributes[] = [
            'id' => $gpuAttributeId,
            'name' => 'gpu',
            'label' => 'GPU',
            'values' => $gpuAttributeValues->values(),
            'options' => $gpuOptions,
            'initial' => $gpuOptions->isNotEmpty() ? $gpuOptions->first()['id'] : null,
        ];
    }

    $firstImage = $product->colorAttributeValues->isNotEmpty() ? $product->colorAttributeValues->first()->getFirstMediaUrl('product-attribute-images', 'large') : '';
    $colorAttributeValuesForGallery = $product->colorAttributeValues->values();
@endphp
<div class="w-full max-w-340 py-10 px-4 sm:px-6 lg:px-8 mx-auto">
    <section class="overflow-hidden bg-white py-11 font-poppins dark:bg-gray-800">
        <div class="max-w-6xl px-4 py-4 mx-auto lg:py-8 md:px-6" x-data="productDetailPage(
            '{{ $firstImage }}',
            {{ $product->price }},
            {{ json_encode($attributes) }},
            {{ json_encode($colorAttributeValuesForGallery) }}
        )">
            <div class="flex flex-wrap -mx-4">
                <div class="w-full mb-8 md:w-1/2 md:mb-0" >
                    <div class="sticky top-0 z-50 overflow-hidden ">
                        @if($hasColorAttribute)
                            <section class="mt-3 border-b-white border-b-2">
                                <div class="relative mb-6 lg:mb-10 lg:h-2/4 ">
                                    <img :src="mainImage" alt="{{$product->name}}" class="object-cover w-full lg:h-full ">
                                </div>
                                <h3 class="text-gray-700 dark:text-gray-400 text-lg">Color:</h3>
                                <div class="flex-wrap hidden md:flex ">
                                    <template x-for="attributeValue in colorAttributeValuesForGallery">
                                        <template x-for="media in attributeValue.media">
                                            <div x-show="selectedAttributes[attributes.find(attr => attr.name === 'color').id] === null || selectedAttributes[attributes.find(attr => attr.name === 'color').id] == attributeValue.attribute_option_id" class="w-1/2 p-2 sm:w-1/4" x-on:click="mainImage = media.original_url">
                                                <img :src="media.original_url" alt="{{$product->name}}" class="object-cover w-full lg:h-20 cursor-pointer hover:border hover:border-blue-500">
                                            </div>
                                        </template>
                                    </template>
                                </div>
                                <div class="flex gap-x-6 items-center">
                                    <template x-for="option in attributes.find(attr => attr.name === 'color').options">
                                        <div class="flex">
                                            <input
                                                type="radio"
                                                :id="'color_' + option.id"
                                                :value="option.id"
                                                x-model="selectedAttributes[attributes.find(attr => attr.name === 'color').id]"
                                                class="shrink-0 mt-0.5 border-gray-200 rounded-full text-blue-600 focus:ring-blue-500 checked:border-blue-500 disabled:opacity-50
                                        disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800">
                                            <label
                                                :for="'color_' + option.id"
                                                x-text="option.name"
                                                class="text-sm text-gray-500 ms-2 dark:text-neutral-400"></label>
                                        </div>
                                    </template>
                                </div>
                            </section>
                        @endif
                        <template x-for="attribute in attributes.filter(attr => attr.name !== 'color')" :key="attribute.name">
                            <section class="mt-3 border-b-white border-b-2">
                                <h3 class="text-gray-700 dark:text-gray-400 text-lg" x-text="attribute.label + ':'"></h3>
                                <div class="flex gap-x-6 items-center">
                                    <select x-model="selectedAttributes[attribute.id]" class="py-3 px-4 pe-9 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600">
                                        <template x-for="option in attribute.options">
                                            <option :value="option.id" x-text="option.name"></option>
                                        </template>
                                    </select>
                                </div>
                            </section>
                        </template>
                    </div>
                </div>
                <div class="w-full px-4 md:w-1/2 ">
                    <div class="lg:pl-20">
                        <div class="mb-8 ">
                            <h2 class="max-w-xl mb-6 text-2xl font-bold dark:text-gray-400 md:text-4xl">
                                {{$product->name}}
                            </h2>
                            <p class="inline-block mb-6 text-4xl font-bold text-gray-700 dark:text-gray-400 ">
                                <span x-text="formatCurrency(currentPrice)"></span>
                                <span class="text-base font-normal text-gray-500 line-through dark:text-gray-400">$1800.99</span>
                            </p>
                            <p class="max-w-md text-gray-700 dark:text-gray-400">
                               {{$product->description}}
                            </p>
                        </div>
                        <div class="w-32 mb-8 ">
                            <label for="" class="w-full pb-1 text-xl font-semibold text-gray-700 border-b border-blue-300 dark:border-gray-600 dark:text-gray-400">Quantity</label>
                            <div class="relative flex flex-row w-full h-10 mt-6 bg-transparent rounded-lg">
                                <button x-on:click="quantity > 1 ? quantity-- : 1"
                                        class="w-20 h-full text-gray-600 bg-gray-300 rounded-l outline-none cursor-pointer dark:hover:bg-gray-700 dark:text-gray-400 hover:text-gray-700 dark:bg-gray-900 hover:bg-gray-400">
                                    <span class="m-auto text-2xl font-thin">-</span>
                                </button>
                                <input type="number" x-model="quantity" readonly
                                       class="flex items-center w-full font-semibold text-center text-gray-700 placeholder-gray-700 bg-gray-300 outline-none dark:text-gray-400 dark:placeholder-gray-400 dark:bg-gray-900 focus:outline-none text-md hover:text-black">
                                <button x-on:click="quantity++"
                                        class="w-20 h-full text-gray-600 bg-gray-300 rounded-r outline-none cursor-pointer dark:hover:bg-gray-700 dark:text-gray-400 dark:bg-gray-900 hover:text-gray-700 hover:bg-gray-400">
                                    <span class="m-auto text-2xl font-thin">+</span>
                                </button>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-4">
                            <x-button
                                variant="add-to-cart"
                                x-on:click="$wire.addToCart({{$product->id}}, quantity, selectedAttributes)"
                                :loading="true"
                                class="lg:w-2/5"
                                icon="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"
                            >
                                Add to cart
                            </x-button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
