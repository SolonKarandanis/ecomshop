<?php

namespace App\Services;

use App\Enums\MessageSeverityEnum;
use Jantinnerezo\LivewireAlert\Enums\Position;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class UiService
{
    public function showMessage(MessageSeverityEnum $messageSeverity,string $title,string|array $text): void
    {
        if(!is_array($text)){
            $text = [$text];
        }
        $alert =LivewireAlert::title($title)
            ->timer(2000)
            ->toast()
            ->position(Position::TopEnd);
        foreach($text as $textItem){
            switch ($messageSeverity){
                case MessageSeverityEnum::INFO:
                    $alert->info()->text($textItem);
                    break;
                case MessageSeverityEnum::SUCCESS:
                    $alert->success()->text($textItem);
                    break;
                case MessageSeverityEnum::WARNING:
                    $alert->warning()->text($textItem);
                    break;
                case MessageSeverityEnum::ERROR:
                    $alert->error()->text($textItem);
            }
        }
        $alert->show();
    }
}
