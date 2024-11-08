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

    // Voeg deze relaties toe als ze er nog niet zijn
    public function sitterProfile()
    {
        return $this->belongsTo(SitterProfile::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function petProfile()
    {
        return $this->belongsTo(PetProfile::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}