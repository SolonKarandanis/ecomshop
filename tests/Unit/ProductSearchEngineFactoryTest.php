<?php

use App\Enums\ProductSearchEngineEnum;
use App\Search\ProductSearchEngineFactory;

it('resolves the FullText engine when the driver is mysql', function () {
    $factory = new ProductSearchEngineFactory('mysql');

    expect($factory->resolveEngine())->toBe(ProductSearchEngineEnum::FullText);
});

it('resolves the Like engine when the driver is sqlite', function () {
    $factory = new ProductSearchEngineFactory('sqlite');

    expect($factory->resolveEngine())->toBe(ProductSearchEngineEnum::Like);
});

it('resolves the Like engine for other non-mysql drivers', function () {
    $factory = new ProductSearchEngineFactory('pgsql');

    expect($factory->resolveEngine())->toBe(ProductSearchEngineEnum::Like);
});
