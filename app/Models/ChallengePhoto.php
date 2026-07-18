<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengePhoto extends Model
{
    protected $fillable = [
        'challenge_id',
        'photo_path',
    ];

    public function challenge()
    {
        return $this->belongsTo(Challenges::class, 'challenge_id');
    }

    public function getUrlAttribute()
    {
        return $this->photo_path ? asset('storage/' . $this->photo_path) : null;
    }
}
