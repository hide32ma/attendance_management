<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Breaks extends Model
{
    use HasFactory;

    public function attendance():BelongsTo
    {
        return $this->belongsTo('App\Models\attendance');
    }
}
