<?php

namespace App\Http\Controllers;

use App\Models\Submissions;
use Illuminate\Http\Request;

class SubmissionsController extends Controller
{
    public function show($id)
    {
        $submission = Submissions::where('submissions.id', '=', $id)
            ->join('challenges', 'submissions.challenge_id', '=', 'challenges.id')
            ->join('candidates', 'submissions.candidate_id', '=', 'candidates.id')
            ->select(
                'submissions.id',
                'submissions.updated_at as submitted_at',
                'candidates.fullname as candidate_name',
                'challenges.title as challenge_title',
                'challenges.description as challenge_description',
                'challenges.points as max_points',
                'challenges.difficulty as challenge_difficulty',
                'challenges.challege_key_file_path as solution_file_path',
            )
            ->first();

        return response()->json(['submission' => $submission]);
    }
}
