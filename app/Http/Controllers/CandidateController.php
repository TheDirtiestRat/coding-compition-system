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
        $candidate = Candidates::where('user_id', '=', $user->id)->first();

        $solvedChallengeIds = $candidate
            ? SolvedChallenges::where('candidate_id', '=', $candidate->id)->pluck('challenge_id')->toArray()
            : [];

        $easy_challenges = Challenges::where("difficulty", "=", 'easy')->latest()->get()
            ->map(fn($challenge) => $challenge->setAttribute('is_solved', in_array($challenge->id, $solvedChallengeIds)));

        $medium_challenges = Challenges::where("difficulty", "=", 'medium')->latest()->get()
            ->map(fn($challenge) => $challenge->setAttribute('is_solved', in_array($challenge->id, $solvedChallengeIds)));

        $hard_challenges = Challenges::where("difficulty", "=", 'hard')->latest()->get()
            ->map(fn($challenge) => $challenge->setAttribute('is_solved', in_array($challenge->id, $solvedChallengeIds)));

        $challenges = Challenges::where("difficulty", "=", 'challenge')->latest()->get()
            ->map(fn($challenge) => $challenge->setAttribute('is_solved', in_array($challenge->id, $solvedChallengeIds)));

        // dd($easy_challenges);

        return view('pages.code-problems', compact('challenges', 'easy_challenges', 'medium_challenges', 'hard_challenges'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function open_code_editor(string $challenge_id = "-1")
    {
        if ($challenge_id >= 0) {
            $details = Challenges::select(['id', 'title', 'description', 'difficulty', 'points'])->where('id', "=", $challenge_id)->get();
            // dd($details);
            return view('pages.code-editor', compact('details'));
        }

        return view('pages.code-editor');
    }

    public function addScorePoints(string $challenge_id)
    {
        // get the user by the candidate details
        $user = Auth::user();
        $candidate = Candidates::where('user_id', '=', $user->id)->first();

        // // check the user first
        if (!$candidate) {
            return response()->json([
                'status' => 'fail',
                'message' => "Can't find this candidate (You must be anuthorize user.)",
                // 'data' => $challenge->challege_key_file_path,
                'match' => false
            ], 200);
            // return back()->with('error', 'Can't find this candidate (You must be anuthorize user.)');
            // return false;
        }

        $challenge = Challenges::find($challenge_id);
        $solved_challenge = SolvedChallenges::where('candidate_id', '=', $candidate->id)
            ->where('challenge_id', '=', $challenge->id)
            ->first();
        // $solved_challenge = SolvedChallenges::where('candidate_id', '=', $candidate->id)->where('challenge_id', '=', $challenge->id)->first();

        // // check first if the challenge is solve by the candidate first
        if ($solved_challenge) {
            return response()->json([
                'status' => 'fail',
                'message' => 'You have already solved this challenge.',
                // 'data' => $challenge->challege_key_file_path,
                'match' => false
            ], 200);
            // return back()->with('info','You have already solved this challenge.');
            // return false;
        }

        // // insert it in the dateabase
        $solved = SolvedChallenges::create([
            'candidate_id' => $candidate->id,
            'challenge_id' => $challenge->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Score is added.',
            'data' => $solved_challenge,
            'match' => true
        ], 200);
        // return true;
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
