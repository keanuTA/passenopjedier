<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'when_needed' => 'datetime',
        'important_info' => 'array'     
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sittingRequests(): HasMany
    {
        return $this->hasMany(SittingRequest::class);
    }

    public function getActiveRequestsAttribute()
    {
        return $this->sittingRequests()
            ->whereIn('status', ['open', 'toegewezen'])
            ->get();
    }
}