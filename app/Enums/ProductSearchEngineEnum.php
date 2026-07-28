<?php

namespace App\Enums;

enum ProductSearchEngineEnum:string
{
    case Like = 'search.engine.like';
    case FullText = 'search.engine.fulltext';
}
