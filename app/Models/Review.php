<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'sitting_request_id',
        'user_id',
        'sitter_profile_id', 
        'rating',
        'review_text'
    ];

    public function sittingRequest()
    {
        return $this->belongsTo(SittingRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sitterProfile()
    {
        return $this->belongsTo(SitterProfile::class);
    }
}