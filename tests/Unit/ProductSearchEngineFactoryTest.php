<?php

use App\Enums\ProductSearchEngineEnum;
use App\Search\ProductSearchEngineFactory;

it('resolves the FullText engine when the driver is mysql and FTS is disabled', function () {
    $factory = new ProductSearchEngineFactory('mysql', false);

    expect($factory->resolveEngine())->toBe(ProductSearchEngineEnum::FullText);
});

it('resolves the Like engine when the driver is sqlite and FTS is disabled', function () {
    $factory = new ProductSearchEngineFactory('sqlite', false);

    expect($factory->resolveEngine())->toBe(ProductSearchEngineEnum::Like);
});

it('resolves the Like engine for other non-mysql drivers when FTS is disabled', function () {
    $factory = new ProductSearchEngineFactory('pgsql', false);

    expect($factory->resolveEngine())->toBe(ProductSearchEngineEnum::Like);
});

it('resolves the Meilisearch engine when FTS is enabled, regardless of driver', function () {
    $factory = new ProductSearchEngineFactory('sqlite', true);

    expect($factory->resolveEngine())->toBe(ProductSearchEngineEnum::Meilisearch);
});

it('resolves the Meilisearch engine even when the driver is mysql, if FTS is enabled', function () {
    $factory = new ProductSearchEngineFactory('mysql', true);

    expect($factory->resolveEngine())->toBe(ProductSearchEngineEnum::Meilisearch);
});
