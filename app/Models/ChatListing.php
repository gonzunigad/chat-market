<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatListing extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function aceppted_by()
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('accepted_at');
    }
}
