<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Application notification sent to users.
 *
 * Note: The table name is 'notifications' (not 'tmc_notifications') to avoid
 * collision with Laravel's built-in Notifiable trait which uses 'notifications'.
 * This model represents TMC-specific notifications, distinct from Laravel's
 * database notification channel.
 */
class TmcNotification extends Model
{
    protected $table = 'notifications';

    protected $fillable = ['user_id', 'title', 'message', 'is_read', 'type', 'link'];

    protected $casts = ['is_read' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
