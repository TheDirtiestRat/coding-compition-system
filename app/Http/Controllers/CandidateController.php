<?php

namespace App\Http\Controllers;

use App\Models\Challenges;
use App\Models\Candidates;
use App\Models\SolvedChallenges;
use App\Models\Submissions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CandidateController extends Controller
{
    public function get_all_challenges()
    {
        $challenges = Challenges::latest()->get();

        return response()->json($challenges);
    }

    public function get_challenge_details($id)
    {
        $challenge = Challenges::select(['title', 'description', 'difficulty', 'points'])->where('id', "=", $id)->get();
        return response()->json($challenge);
    }

    /**
     * Display a listing of the resource.
     */
    public function code_problems()
    {
        $user = Auth::user();
        $candidate = Candidates::where('user_id', $user->id)->first();

        $baseQuery = fn($difficulty) =>
            Challenges::where('challenges.difficulty', $difficulty)
                ->leftJoin('submissions', function ($join) use ($candidate) {
                    $join->on('submissions.challenge_id', '=', 'challenges.id')
                        ->where('submissions.candidate_id', '=', $candidate->id);
                })
                ->selectRaw("
                    challenges.*,
                    submissions.score,
                    CASE
                        WHEN submissions.is_score_submitted = 1 THEN 'reviewed'
                        WHEN submissions.id IS NOT NULL THEN 'submitted'
                        ELSE 'not_started'
                    END AS status
                ")
                ->get();

        $easy_challenges = $baseQuery('easy');
        $medium_challenges = $baseQuery('medium');
        $hard_challenges = $baseQuery('hard');
        $challenges = $baseQuery('challenge');

        return view('pages.code-problems', compact(
            'challenges',
            'easy_challenges',
            'medium_challenges',
            'hard_challenges'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function open_code_editor(string $challenge_id = "-1")
    {
        if ($challenge_id >= 0) {
            $details =
                Challenges::leftJoin('submissions', 'submissions.challenge_id', '=', 'challenges.id')
                    ->selectRaw("
                        challenges.*,
                        submissions.score,
                        CASE
                            WHEN submissions.is_score_submitted = 1 THEN 'reviewed'
                            WHEN submissions.id IS NOT NULL THEN 'submitted'
                            ELSE 'not_started'
                        END AS status
                    ")
                    ->where('challenges.id', "=", $challenge_id)
                    ->get();
            return view('pages.code-editor', compact('details'));
        }

        return view('pages.code-editor');
    }

    public function submitCodeForReview(Request $request)
    {
        // Get candidate
        $user = Auth::user();
        $candidate = Candidates::where('user_id', '=', $user->id)->first();

        $request->validate([
            'challenge_id' => 'required|string',
            'code' => 'required|string',
        ]);
        $challenge_id = $request->input('challenge_id');
        $challenge = Challenges::find($challenge_id);

        if (!isset($candidate)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Candidate not found.'
            ], 404);
        }

        if (!isset($challenge)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Challenge not found.'
            ], 404);
        }

        Submissions::upsert(
            [
                'candidate_id' => $candidate->id,
                'challenge_id' => $challenge_id,
                'code' => $request->input('code')
            ],
            uniqueBy: ['candidate_id', 'challenge_id'],
            update: ['code']
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Code submitted successfully. Awaiting review...',
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */

    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        //
    }
}
