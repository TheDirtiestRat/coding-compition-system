<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Candidates;
use App\Models\Challenges;
use App\Models\Submissions;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function dashboard()
    {
        // gets the list of candidates with their total points
        $candidates = Candidates::leftJoin('solved_challenges', 'candidates.id', '=', 'solved_challenges.candidate_id')
            ->leftJoin('challenges', 'solved_challenges.challenge_id', '=', 'challenges.id')
            ->select(
                'candidates.*',
                DB::raw('COALESCE(SUM(points), 0) as total_points'),
            )
            ->groupBy('candidates.id')
            ->orderByDesc('total_points')
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
                'challenges.description as challenge_description',
                'challenges.points as max_points',
                'challenges.difficulty as challenge_difficulty',
                'challenges.challege_key_file_path as solution_file_path',
            )
            ->orderByDesc('submitted_at')
            ->get();

        return view('admin.submissions', compact('submissions'));
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

        return view('admin.challenges', compact('challenges'));
    }

    public function get_challenge_details($id)
    {
        $challenge = Challenges::where('id', "=", $id)->get();
        // $result = $challenge->to->toJson(JSON_PRETTY_PRINT);
        return response()->json($challenge);
    }

    public function create_challenge(Request $request)
    {
        $validatedData = $request->validate([
            // 'challenge_id' => 'required|exists:challenges,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'difficulty' => 'required',
            'points' => 'required',
            'file_answer_key' => 'required',
        ]);

        try {
            // Save answer key file
            $path = "";
            if ($request->hasFile('file_answer_key')) {
                $answer_key_file = $request->file('file_answer_key');
                $filename = strtolower(str_replace(' ', '', $validatedData['title'])) . '.' . $answer_key_file->getClientOriginalExtension();
                // $filename = uniqid('answer_key_' . trim($validatedData['title'])) . '.' . $answer_key_file->getClientOriginalExtension();
                $path = $answer_key_file->storeAs('master-files', $filename, 'local');
            }

            $challenge = Challenges::create([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'difficulty' => $validatedData['difficulty'],
                'points' => $validatedData['points'],
                'challege_key_file_path' => $path,
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Error creating the Challenge: ' . $e->getMessage())->withInput();
        }

        return redirect('/admin/challenges')->with('success', 'A Challenge is added!');
    }

    public function update_challenge(Request $request)
    {
        $validatedData = $request->validate([
            'challenge_id' => 'required|exists:challenges,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'difficulty' => 'required',
            'points' => 'required',
            // 'file_answer_key' => 'required',
        ]);

        try {
            $challenge = Challenges::findOrFail($validatedData['challenge_id']);

            $challenge->update([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'difficulty' => $validatedData['difficulty'],
                'points' => $validatedData['points'],
            ]);
        } catch (\Exception $e) {
            return redirect('/admin/challenges')->with('error', 'Error updating the Challenge: ' . $e->getMessage());
        }

        return redirect('/admin/challenges')->with('success', 'A Challenge is updated successfully!');
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
