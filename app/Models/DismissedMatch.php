<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DismissedMatch extends Model
{
    protected $fillable = ['user_id', 'lost_item_id', 'found_item_id'];
}
