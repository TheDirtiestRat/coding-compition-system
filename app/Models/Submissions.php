<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submissions extends Model
{
    protected $table = 'submissions';

    protected $fillable = [
        'candidate_id',
        'challenge_id',
        'score',
        'code',
        'is_score_submitted',
        'test_case_results',
        'is_auto_reviewed',
        'scored_at',
    ];

    protected function casts(): array
    {
        return [
            'is_score_submitted' => 'boolean',
            'is_auto_reviewed' => 'boolean',
            'scored_at' => 'datetime',
            'test_case_results' => 'array',
        ];
    }

    public function challenge()
    {
        return $this->belongsTo(Challenges::class, 'challenge_id');
    }

    public function candidate()
    {
        return $this->belongsTo(Candidates::class, 'candidate_id');
    }
}
