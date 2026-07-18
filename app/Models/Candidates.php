<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Candidates extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'fullname',
        'group',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Events::class, 'event_id');
    }

    public function submissions()
    {
        return $this->hasMany(Submissions::class, 'candidate_id');
    }
}
