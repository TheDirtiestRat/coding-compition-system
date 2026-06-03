<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Candidates;
use App\Models\Challenges;
use App\Models\Submissions;
use App\Models\User;
use App\Models\Events;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;

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

    public function results()
    {
        $active_event = Events::select()->where("isActive", "=", true)->get()->first();
        // gets the list of candidates with their total points
        $candidates =
            Candidates::leftJoin('submissions', 'candidates.id', '=', 'submissions.candidate_id')
            ->select(
                'candidates.*',
                DB::raw('COALESCE(SUM(submissions.score), 0) as total_score'),
            )->where('event_id','=',$active_event->id)
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

        return view('pages.results', compact('candidates'));
    }

    public function events()
    {
        $events = DB::table('events')->get('*');

        return view('admin.events', compact('events'));
    }

    public function candidates()
    {
        $active_event = Events::select()->where("isActive", "=", true)->get()->first();
        $candidates = Candidates::select()->where('event_id','=',$active_event->id)->with('user')->get();
        // $candidates = Candidates::join('events', 'candidates.event_id', "=", $active_event->id)->with('user')->get();
        // dd($candidates);
        $events = DB::table('events')->get();

        return view('admin.candidates', compact('candidates', 'events'));
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
                'challenges.description',
            )
            ->orderByDesc('submitted_at')
            ->get();

        return response()->json(['submission' => $submissions]);
        // return view('admin.submissions', compact('submissions'));
    }

    public function add_event(Request $request)
    {
        $validatedData = $request->validate([
            'event_title' => 'required|string|max:255',
        ]);

        try {
            $event = DB::table('events')->insert([
                'title' => $validatedData['event_title'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        } catch (\Exception $e) {
            return redirect('/admin/events')->with('error', 'Error creating Event: ' . $e->getMessage());
        }


        return redirect('/admin/events')->with('success', 'A Event is Created!');
    }

    public function activate_event(Request $request)
    {
        $validatedData = $request->validate([
            'event_id' => 'required|exists:events,id',
            'isActive' => 'required',
        ]);

        // deactivates all active events and activates the event selected so that there can only one active event at a time.
        DB::table('events')->where('isActive', '=', true)->update([
            'isActive' => false,
            'updated_at' => Carbon::now(),
        ]);

        $event = DB::table('events')->where('id', '=', $validatedData['event_id'])->update([
            'isActive' => true,
            'updated_at' => Carbon::now(),
        ]);

        $active_msg = ($validatedData['isActive']) ? "Activated" : "DeActivated!";

        return redirect('/admin/events')->with('success', 'Event is ' . $active_msg . ' successfully!');
    }

    public function update_event(Request $request)
    {
        $validatedData = $request->validate([
            'event_id' => 'required|exists:events,id',
            'edit_title' => 'required|string|max:255',
        ]);

        try {
            $event = DB::table('events')->where('id', '=', $validatedData['event_id'])->update([
                'title' => $validatedData['edit_title'],
                'updated_at' => Carbon::now(),
            ]);
        } catch (\Exception $e) {
            return redirect('/admin/events')->with('error', 'Error updating Event : ' . $e->getMessage());
        }

        return redirect('/admin/events')->with('success', 'Event is updated successfully!');
    }

    public function delete_event(string $event_id)
    {
        DB::table('events')->delete($event_id);

        // Redirect back with a success message
        return redirect('/admin/events')->with('success', 'Event is and the following connected to it is deleted successfully.');
    }

    public function add_candidate(Request $request)
    {
        $validatedData = $request->validate([
            'event_id' => 'required|exists:events,id',
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
                'event_id' => $validatedData['event_id'],
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
            'event_id' => 'required|exists:events,id',
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
                'event_id' => $validatedData['event_id'],
                'fullname' => $validatedData['edit_name'],
            ]);
        } catch (\Exception $e) {
            return redirect('/admin/candidates')->with('error', 'Error updating competitor: ' . $e->getMessage());
        }

        return redirect('/admin/candidates')->with('success', 'Competitor updated successfully!');
    }

    public function view_candidate(string $candidate_id)
    {
        $candidate = Candidates::where('id', '=', $candidate_id)->first();
        $can_name = Candidates::select('fullname')->where('id', '=', $candidate_id)->first();
        $user_acc = User::findOr($candidate->user_id);
        $events = Events::select()->get();

        $solved_challenges = Challenges::select('submissions.id', 'challenges.title', 'challenges.points', 'challenges.language', 'challenges.difficulty', 'challenges.created_at', 'submissions.challenge_id', 'submissions.score')->join('submissions', function ($join) use ($candidate) {
            $join->on('submissions.challenge_id', '=', 'challenges.id')
                ->where('submissions.candidate_id', '=', $candidate->id);
        })
            ->get();

        // dd($solved_challenges);

        $candidates = Candidates::with('user')->latest()->get();

        return view('admin.candidates', compact('candidates', 'solved_challenges', 'can_name', 'user_acc', 'events'));
    }

    public function get_candidate_codes(string $id)
    {
        $code_solutions = Submissions::where('submissions.id', $id)
            ->join('challenges', 'submissions.challenge_id', '=', 'challenges.id')
            ->select('submissions.code', 'challenges.code_solution', 'challenges.description')
            ->first();
        // $code_solutions = Submissions::select()->where('id', "=", $id)->join('challenges','submissions.challenge_id', "=", "challenges.id")->get()->first();
        // $result = $challenge->to->toJson(JSON_PRETTY_PRINT);
        return response()->json($code_solutions);
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
        $event = Events::where('isActive', true)->first();
        $challenges = Challenges::where('challenges.event_id', "=", $event->id)->latest()->get();
        $last_id = Challenges::latest()->first('id');
        $events = DB::table('events')->get();

        return view('admin.challenges', compact('challenges', 'last_id', 'events'));
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
            'event_id' => 'required|exists:events,id',
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
                    'event_id' => $validatedData['event_id'],
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
