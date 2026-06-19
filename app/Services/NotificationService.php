<?php

namespace App\Services;

use App\Repositories\NotificationRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationService
{

    public function __construct(
        private readonly NotificationRepository $notificationRepository
    ){}

    public function getUsersNotifications(int $userId):LengthAwarePaginator|array{
        return $this->notificationRepository->getUsersNotifications($userId);
    }
}
