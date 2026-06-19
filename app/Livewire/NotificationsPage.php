<?php

namespace App\Livewire;

use App\Services\NotificationService;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationsPage extends Component
{
    use WithPagination;

    protected NotificationService $notificationService;

    public function boot(NotificationService $notificationService){
        $this->notificationService = $notificationService;
    }

    public function render()
    {
        $notifications = $this->notificationService->getUsersNotifications(auth()->id());

        return view('livewire.notifications-page', compact('notifications'));
    }
}
