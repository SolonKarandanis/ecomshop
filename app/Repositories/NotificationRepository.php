<?php

namespace App\Repositories;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationRepository
{

    public function modelQuery(): Builder| Notification{
        return Notification::query();
    }

    public function getUsersNotifications(int $userId):LengthAwarePaginator|array{
        return $this->modelQuery()->forUser($userId)->orderBy('created_at', 'desc')->paginate(20);
    }
}
