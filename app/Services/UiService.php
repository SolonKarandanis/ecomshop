<?php

namespace App\Services;

use App\Enums\MessageSeverityEnum;
use Jantinnerezo\LivewireAlert\Enums\Position;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class UiService
{
    public function addToCartError(): void
    {
        $this->showMessage(
            MessageSeverityEnum::ERROR,
            __('messages.add_to_cart.title'),
            __('messages.add_to_cart.unauthorized')
        );
    }
    public function showMessage(MessageSeverityEnum $messageSeverity,string $title,string|array $text): void
    {
        if(!is_array($text)){
            $text = [$text];
        }
        $alert =LivewireAlert::title($title)
            ->timer(2000)
            ->toast()
            ->position(Position::TopEnd);
        collect($text)->each(function ($textItem) use ($alert, $messageSeverity) {
            $alert->text($textItem);
            match ($messageSeverity) {
                MessageSeverityEnum::INFO    => $alert->info(),
                MessageSeverityEnum::SUCCESS => $alert->success(),
                MessageSeverityEnum::WARNING => $alert->warning(),
                MessageSeverityEnum::ERROR   => $alert->error(),
            };
        });
        $alert->show();
    }
}
