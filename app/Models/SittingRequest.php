<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SittingRequest extends Model
{
    protected $fillable = [
        'pet_profile_id',
        'user_id',
        'sitter_profile_id',
        'start_datum',
        'eind_datum',
        'uurtarief',
        'extra_informatie',
        'status'
    ];

    protected $casts = [
        'start_datum' => 'datetime',
        'eind_datum' => 'datetime',
        'uurtarief' => 'decimal:2'
    ];

    // Relatie met PetProfile
    public function petProfile()
    {
        return $this->belongsTo(PetProfile::class);
    }

    // Relatie met User (eigenaar)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relatie met SitterProfile (oppas) - Deze ontbrak!
    public function sitterProfile()
    {
        return $this->belongsTo(SitterProfile::class);
    }

    // Relatie met reacties van oppassen
    public function responses()
    {
        return $this->hasMany(SittingResponse::class);
    }

    // Relatie met review
    public function review()
    {
        return $this->hasOne(Review::class);
    }

    // Helper method voor status checks
    public function isOpen()
    {
        return $this->status === 'open';
    }

    public function isAssigned()
    {
        return $this->status === 'toegewezen';
    }
}