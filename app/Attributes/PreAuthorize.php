<?php

namespace App\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
class PreAuthorize
{
    public function __construct(public readonly string $gate) {}
}
