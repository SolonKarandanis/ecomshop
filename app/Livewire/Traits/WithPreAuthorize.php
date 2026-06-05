<?php

namespace App\Livewire\Traits;

use App\Attributes\PreAuthorize;
use Illuminate\Support\Facades\Gate;
use ReflectionMethod;

trait WithPreAuthorize
{
    // Reads the #[PreAuthorize] attribute from the named method and checks the gate.
    // Pass __FUNCTION__ from the call site so reflection targets the right method.
    protected function isPreAuthorized(string $method): bool
    {
        $attrs = (new ReflectionMethod($this, $method))->getAttributes(PreAuthorize::class);

        // No attribute on this method — allow through.
        if (empty($attrs)) {
            return true;
        }

        $gate = $attrs[0]->newInstance()->gate;

        return Gate::allows($gate);
    }
}
