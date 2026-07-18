<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestCase extends Model
{
    protected $fillable = [
        'challenge_id',
        'input',
        'expected_output',
        'points',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function challenge()
    {
        return $this->belongsTo(Challenges::class, 'challenge_id');
    }
}
