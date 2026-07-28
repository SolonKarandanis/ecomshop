<?php

namespace App\Search;

use App\Enums\ProductSearchEngineEnum;

class ProductSearchEngineFactory
{
    public function __construct(
        private readonly string $driver
    ){}
    public function make():ProductSearchEngineInterface{
        return match ($this->resolveEngine()){
            ProductSearchEngineEnum::Like=>app(LikeProductSearchEngine::class),
            ProductSearchEngineEnum::FullText => app(FullTextProductSearchEngine::class),
        };
    }

    public function resolveEngine():ProductSearchEngineEnum{
        return $this->driver === 'mysql'
            ? ProductSearchEngineEnum::FullText
            : ProductSearchEngineEnum::Like;
    }
}
