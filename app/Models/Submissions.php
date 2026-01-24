<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submissions extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'submissions';

    protected $fillable = [
        'candidate_id',
        'challenge_id',
        'score',
        'code',
        'is_score_submitted'
    ];

    public function challenge()
    {
        return $this->belongsTo(Challenges::class, 'challenge_id');
    }
}
