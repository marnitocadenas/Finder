<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WatchedFoundItem extends Model
{
    protected $fillable = ['user_id', 'found_item_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function foundItem(): BelongsTo
    {
        return $this->belongsTo(FoundItem::class);
    }
}
