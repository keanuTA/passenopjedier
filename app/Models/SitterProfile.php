<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class SitterProfile extends Model
{
    protected $fillable = [
        'ervaring',
        'over_mij',
        'huisdier_voorkeuren',
        'beschikbare_tijden',
        'uurtarief',
        'profielfoto_pad',
        'video_intro_pad',
        'is_beschikbaar',
        'service_gebied',
        'user_id'
    ];

    protected $casts = [
        'huisdier_voorkeuren' => 'array',
        'beschikbare_tijden' => 'array',
        'service_gebied' => 'array',
        'is_beschikbaar' => 'boolean',
        'uurtarief' => 'float'  // Veranderd van decimal:2 naar float
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sittingRequests(): HasMany
    {
        return $this->hasMany(SittingRequest::class);
    }

    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(
            Review::class,
            SittingRequest::class,
            'sitter_profile_id', // Foreign key on sitting_requests table
            'sitting_request_id', // Foreign key on reviews table
            'id', // Local key on sitter_profiles table
            'id'  // Local key on sitting_requests table
        );
    }
}