<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification as BaseNotification;

/**
 * @property string $id
 * @property string $type
 * @property string $notifiable_type
 * @property int $notifiable_id
 * @property array $data
 * @property Carbon|null $read_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model $notifiable
 */
class Notification extends BaseNotification
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * Scope a query to only include unread notifications.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOnlyUnread(Builder $query): Builder
    {
        $query->whereNull('read_at');

        return $query;
    }

    /**
     * Scope a query to only include read notifications.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOnlyRead(Builder $query): Builder
    {
        $query->whereNotNull('read_at');

        return $query;
    }

    /**
     * Scope a query to only include notifications of a certain type.
     *
     * @param Builder $query
     * @param string $type
     * @return Builder
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        $query->where('type', $type);

        return $query;
    }

    /**
     * Scope a query to only include notifications for a specific user.
     *
     * @param Builder $query
     * @param int|string|User $user
     * @return Builder
     */
    public function scopeForUser(Builder $query, User|int|string $user): Builder
    {
        $userId = $user instanceof User ? $user->id : $user;
        $query->where('notifiable_type', User::class)
            ->where('notifiable_id', $userId);

        return $query;
    }
}
