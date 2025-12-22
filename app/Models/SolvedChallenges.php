<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolvedChallenges extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'solved_challenges';

    protected $fillable = [
        'candidate_id',
        'challenge_id',
    ];
}
