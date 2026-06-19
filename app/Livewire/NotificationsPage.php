<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class NotificationsPage extends Component
{
    use WithPagination;

    public function render()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('livewire.notifications-page', compact('notifications'));
    }
}
