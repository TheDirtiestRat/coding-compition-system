<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Challenges extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'points',
        'difficulty',

        'challege_key_file_path',
    ];
}
