<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetProfile extends Model
{
    protected $fillable = [
        'name',
        'type',
        'when_needed',
        'duration',
        'hourly_rate',
        'important_info',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}