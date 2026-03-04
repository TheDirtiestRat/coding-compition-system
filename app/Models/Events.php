<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Events extends Model
{
    //
    protected $table = 'events';

    protected $fillable = [
        'title',
        'isActive',
    ];

    public static function getActiveEvent()
    {
        return self::where('isActive', true)->first();
    }

    public static function getAllEvents()
    {
        return self::get();
    }
}
