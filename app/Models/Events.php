<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Events extends Model
{
    protected $table = 'events';

    protected $fillable = [
        'title',
        'isActive',
    ];

    public function candidates()
    {
        return $this->hasMany(Candidates::class, 'event_id');
    }

    public function challenges()
    {
        return $this->hasMany(Challenges::class, 'event_id');
    }

    public static function getActiveEvent()
    {
        return self::where('isActive', true)->first();
    }

    public static function getAllEvents()
    {
        return self::all();
    }
}
