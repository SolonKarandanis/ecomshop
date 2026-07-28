<?php

namespace App\Search;

use App\Enums\ProductSearchEngineEnum;

class ProductSearchEngineFactory
{
    public function make(ProductSearchEngineEnum $engine):ProductSearchEngineInterface{
        return match ($engine){
            ProductSearchEngineEnum::Like=>app(LikeProductSearchEngine::class),
        };
    }
}
