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

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the pet profiles for the user.
     */
    public function petProfiles()
    {
        return $this->hasMany(PetProfile::class);
    }

    /**
     * Get the sitting requests where the user is the sitter.
     */
    public function sittingRequests(): HasMany
    {
        return $this->hasMany(SittingRequest::class, 'sitter_id');
    }
    
    public function sitterProfile()
    {
        return $this->hasOne(SitterProfile::class);
    }
}