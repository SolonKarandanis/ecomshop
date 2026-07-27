<?php

namespace App\Livewire\Traits;

use App\Enums\MessageSeverityEnum;
use Illuminate\Support\Facades\Log;
use Throwable;

trait WithMessages
{
    protected function handleSuccess(?string $dispatchEvent, string $msgTitle, string $msgSuccess): void
    {
        if ($dispatchEvent) {
            $this->dispatch($dispatchEvent);
        }
        $this->uiService->showMessage(
            MessageSeverityEnum::SUCCESS,
            $msgTitle,
            $msgSuccess
        );
    }

    protected function handleError(string $msgTitle, string $msgFail, Throwable $e): void
    {
        Log::error($e->getMessage());
        $this->uiService->showMessage(
            MessageSeverityEnum::ERROR,
            $msgTitle,
            $msgFail
        );
    }
}
