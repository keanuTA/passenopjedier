<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'sitting_request_id',
        'comment',
        'rating'
    ];

    // Relatie met SittingRequest
    public function sittingRequest(): BelongsTo
    {
        return $this->belongsTo(SittingRequest::class);
    }
}