<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Challenges extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'title',
        'description',
        'difficulty',
        'language',
        'language_id',
        'points',
        'code_solution',
        'output_mode',
        'output_tolerance',
    ];

    protected $casts = [
        'output_tolerance' => 'float',
    ];

    public function submissions()
    {
        return $this->hasMany(Submissions::class, 'challenge_id');
    }

    public function event()
    {
        return $this->belongsTo(Events::class, 'event_id');
    }

    public function photos()
    {
        return $this->hasMany(ChallengePhoto::class, 'challenge_id');
    }

    public function testCases()
    {
        return $this->hasMany(TestCase::class, 'challenge_id');
    }

    public function activeTestCases()
    {
        return $this->hasMany(TestCase::class, 'challenge_id')->where('is_active', true);
    }

    public function languageModel()
    {
        return $this->belongsTo(ProgrammingLanguage::class, 'language_id');
    }
}
