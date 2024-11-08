<?php

namespace App\Models;

use App\Models\SitterProfile;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'is_blocked' 
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'is_blocked' => 'boolean'  
    ];

    public function petProfiles()
    {
        return $this->hasMany(PetProfile::class);
    }

    public function sittingRequests(): HasMany
    {
        return $this->hasMany(SittingRequest::class, 'sitter_id');
    }
    
    public function sitterProfile()
    {
        return $this->hasOne(SitterProfile::class);
    }

    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }
}