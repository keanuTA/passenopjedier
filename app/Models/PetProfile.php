<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetProfile extends Model
{
    protected $fillable = [
        'user_id',
        'name',           
        'type',           
        'when_needed',
        'duration',
        'hourly_rate',
        'important_info'
    ];

    protected $casts = [
        'belangrijke_info' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sittingRequests()
    {
        return $this->hasMany(SittingRequest::class);
    }

    // Helper method voor actieve oppasvragen
    public function getActiveRequestsAttribute()
    {
        return $this->sittingRequests()
            ->whereIn('status', ['open', 'toegewezen'])
            ->get();
    }
}