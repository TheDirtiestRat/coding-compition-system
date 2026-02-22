<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Candidates;
use App\Models\Challenges;
use App\Models\Submissions;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function dashboard()
    {
        // gets the list of candidates with their total points
        $candidates =
            Candidates::leftJoin('submissions', 'candidates.id', '=', 'submissions.candidate_id')
                ->select(
                    'candidates.*',
                    DB::raw('COALESCE(SUM(submissions.score), 0) as total_score'),
                )
                ->groupBy(
                    'candidates.id',
                    'candidates.fullname',
                    'candidates.group',
                    'candidates.user_id',
                    'candidates.created_at',
                    'candidates.updated_at',
                )
                ->orderByDesc('total_score')
                ->get();

        $previousPoints = null;
        $currentRank = 0;
        $actualCount = 0;

        $rankedCandidates = $candidates->sortByDesc('total_points')
            ->values()
            ->map(function ($candidate) use (&$previousPoints, &$currentRank, &$actualCount) {
                $actualCount++;

                // If points are different from the last person, update the rank to the current count
                if ($candidate->total_points !== $previousPoints) {
                    $currentRank = $actualCount;
                }

                $candidate->rank = $currentRank;
                $previousPoints = $candidate->total_points;

                return $candidate;
            });

        return view('admin.dashbaord', compact('candidates'));
    }

    public function candidates()
    {
        $candidates = Candidates::with('user')->latest()->get();

        return view('admin.candidates', compact('candidates'));
    }

    public function submissions()
    {
        $submissions = Submissions::where('is_score_submitted', '=', 0)
            ->join('challenges', 'submissions.challenge_id', '=', 'challenges.id')
            ->join('candidates', 'submissions.candidate_id', '=', 'candidates.id')
            ->select(
                'submissions.id',
                'submissions.updated_at as submitted_at',
                'candidates.fullname as candidate_name',
                'challenges.title as challenge_title',
                'challenges.points as max_points',
                'challenges.difficulty as challenge_difficulty',
                'challenges.language as challenge_language',
            )
            ->orderByDesc('submitted_at')
            ->get();

        return view('admin.submissions', compact('submissions'));
    }

    public function submissions_api()
    {
        $submissions = Submissions::where('is_score_submitted', '=', 0)
            ->join('challenges', 'submissions.challenge_id', '=', 'challenges.id')
            ->join('candidates', 'submissions.candidate_id', '=', 'candidates.id')
            ->select(
                'submissions.id',
                'submissions.updated_at as submitted_at',
                'candidates.fullname as candidate_name',
                'challenges.title as challenge_title',
                'challenges.points as max_points',
                'challenges.difficulty as challenge_difficulty',
                'challenges.language as challenge_language',
            )
            ->orderByDesc('submitted_at')
            ->get();

        return response()->json(['submission' => $submissions]);
        // return view('admin.submissions', compact('submissions'));
    }

    public function add_candidate(Request $request)
    {
        $validatedData = $request->validate([
            'candidate_name' => 'required|string|max:255',
            'username' => 'required|string|max:128',
            'user_password' => 'required',
        ]);

        try {
            $user = User::factory()->create([
                'name' => trim($validatedData['username']),
                'email' => trim(strtolower($validatedData['username'])) . '@hungergame.com',
                'password' => $validatedData['user_password'],
            ]);

            $candidate = Candidates::create([
                'fullname' => $validatedData['candidate_name'],
                'group' => "none",
                'user_id' => $user->id,
            ]);
        } catch (\Exception $e) {
            return redirect('/admin/candidates')->with('error', 'Error creating competitor: ' . $e->getMessage());
        }

        // if (!$user || !$candidate) {
        //     return redirect('/admin/candidates')->with('error', 'Failed to create a Competitor.');
        // }

        return redirect('/admin/candidates')->with('success', 'A Competitor is added!');
    }

    public function update_candidate(Request $request)
    {
        $validatedData = $request->validate([
            'candidate_id' => 'required|exists:candidates,id',
            'edit_name' => 'required|string|max:255',
            'edit_username' => 'required|string|max:128',
            'edit_user_password' => 'required',
        ]);

        try {
            $candidate = Candidates::findOrFail($validatedData['candidate_id']);
            $user = $candidate->user;

            $user->update([
                'name' => trim($validatedData['edit_username']),
                'email' => trim(strtolower($validatedData['edit_username'])) . '@hungergame.com',
                'password' => !empty($validatedData['edit_user_password']) ? $validatedData['edit_user_password'] : $user->password,
            ]);

            if ($validatedData['edit_user_password']) {
                $user->update([
                    'password' => $validatedData['edit_user_password'],
                ]);
            }

            $candidate->update([
                'fullname' => $validatedData['edit_name'],
            ]);
        } catch (\Exception $e) {
            return redirect('/admin/candidates')->with('error', 'Error updating competitor: ' . $e->getMessage());
        }

        return redirect('/admin/candidates')->with('success', 'Competitor updated successfully!');
    }

    public function view_candidate(string $candidate_id)
    {
        $candidate = Candidates::where('id','=', $candidate_id)->first();
        $can_name = Candidates::select('fullname')->where('id','=', $candidate_id)->first();
        $user_acc = User::findOr($candidate->user_id);

        $solved_challenges = Challenges::select('challenges.id','challenges.title','challenges.points','challenges.language','challenges.difficulty','challenges.created_at','submissions.challenge_id','submissions.score')->join('submissions', function ($join) use ($candidate) {
                    $join->on('submissions.challenge_id', '=', 'challenges.id')
                        ->where('submissions.candidate_id', '=', $candidate->id);
                })
                ->get();

        // dd($solved_challenges);

        $candidates = Candidates::with('user')->latest()->get();

        return view('admin.candidates', compact('candidates', 'solved_challenges', 'can_name','user_acc'));
    }

    public function delete_candidate(string $candidate_id)
    {
        // Delete the candidate record
        Candidates::destroy($candidate_id);

        // Redirect back with a success message
        return redirect('/admin/candidates')->with('success', 'User and Candidate deleted successfully.');
    }

    public function challenges()
    {
        $challenges = Challenges::latest()->get();
        $last_id = Challenges::latest()->first('id');


        return view('admin.challenges', compact('challenges','last_id'));
    }

    public function get_challenge_details($id)
    {
        $challenge = Challenges::where('id', "=", $id)->get()->first();
        // $result = $challenge->to->toJson(JSON_PRETTY_PRINT);
        return response()->json($challenge);
    }

    public function save_challenge(Request $request)
    {
        $validatedData = $request->validate([
            'challenge_id' => 'required',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'difficulty' => 'required|in:easy,medium,hard,challenge',
            'language' => 'required|in:java,python',
            'points' => 'required|integer|min:1',
            'code_solution' => 'required|string',
        ]);

        try {
            $challenge = Challenges::updateOrCreate(
                ['id' => $validatedData['challenge_id'] ?? null],
                [
                    'title' => $validatedData['title'],
                    'description' => $validatedData['description'],
                    'difficulty' => $validatedData['difficulty'],
                    'language' => $validatedData['language'],
                    'points' => $validatedData['points'],
                    'code_solution' => $validatedData['code_solution'],
                ]
            );
        } catch (\Throwable $e) {
            Log::error('Failed to save challenge', [
                'exception' => $e,
                'payload' => $validatedData,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Something went wrong while saving the challenge.');
        }

        return redirect('/admin/challenges')->with(
            'success',
            $challenge->wasRecentlyCreated
            ? 'Challenge created successfully!'
            : 'Challenge updated successfully!'
        );
    }

    public function delete_challenge(string $id)
    {
        $challenge = Challenges::where('id', $id)->first();

        // Delete the candidate record
        Challenges::destroy($id);


        // deletets the uploaded files
        if (Storage::disk('local')->exists('master-files/' . $challenge->challege_key_file_path)) {
            Storage::disk('local')->delete('master-files/' . $challenge->challege_key_file_path);
        }

        // Redirect back with a success message
        return redirect('/admin/challenges')->with('success', 'A Challenge is deleted successfully.');
    }
}
