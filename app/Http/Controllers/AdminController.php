<?php

namespace App\Http\Controllers;

use App\Models\Candidates;
use App\Models\Challenges;
use App\Models\Events;
use App\Models\ProgrammingLanguage;
use App\Models\Submissions;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function dashboard()
    {
        $candidates = $this->ranked_candidates()->get();

        return view('admin.dashboard', compact('candidates'));
    }

    public function results()
    {
        $active_event = Events::where('isActive', true)->first();

        if (! $active_event) {
            return view('pages.results', ['candidates' => collect()]);
        }

        $candidates = $this->ranked_candidates()->where('event_id', $active_event->id)->get();

        return view('pages.results', compact('candidates'));
    }

    private function ranked_candidates()
    {
        return Candidates::select('candidates.*')->selectSub(
            Submissions::selectRaw('COALESCE(SUM(score), 0)')
                ->whereColumn('candidate_id', 'candidates.id')
                ->whereHas('challenge', fn ($query) => $query->whereColumn('challenges.event_id', 'candidates.event_id')),
            'total_score'
        )->orderByDesc('total_score');
    }

    public function events()
    {
        $events = Events::all();

        return view('admin.events', compact('events'));
    }

    public function candidates()
    {
        $active_event = Events::where('isActive', true)->first();

        if (! $active_event) {
            return view('admin.candidates', ['candidates' => collect(), 'events' => Events::all()]);
        }

        $candidates = Candidates::where('event_id', $active_event->id)->with('user')->get();
        $events = Events::all();

        return view('admin.candidates', compact('candidates', 'events'));
    }

    public function submissions()
    {
        $submissions = $this->pending_submissions()->get();

        return view('admin.submissions', compact('submissions'));
    }

    public function submissions_api()
    {
        $submissions = $this->pending_submissions()->addSelect('challenges.description')->get();

        return response()->json(['submission' => $submissions]);
    }

    private function pending_submissions()
    {
        return Submissions::where('is_score_submitted', '=', 0)
            ->where('is_auto_reviewed', '=', 0)
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
            ->orderByDesc('submitted_at');
    }

    public function add_event(Request $request)
    {
        $validatedData = $request->validate([
            'event_title' => 'required|string|max:255',
        ]);

        try {
            Events::create([
                'title' => $validatedData['event_title'],
            ]);
        } catch (\Exception $e) {
            return redirect('/admin/events')->with('error', 'Error creating Event: '.$e->getMessage());
        }

        return redirect('/admin/events')->with('success', 'A Event is Created!');
    }

    public function activate_event(Request $request)
    {
        $validatedData = $request->validate([
            'event_id' => 'required|exists:events,id',
            'isActive' => 'required|in:true,false',
        ]);

        DB::transaction(function () use ($validatedData) {
            Events::where('isActive', true)->update(['isActive' => false]);
            Events::whereKey($validatedData['event_id'])->update(['isActive' => true]);
        });

        return redirect('/admin/events')->with('success', 'Event activated successfully!');
    }

    public function update_event(Request $request)
    {
        $validatedData = $request->validate([
            'event_id' => 'required|exists:events,id',
            'edit_title' => 'required|string|max:255',
        ]);

        try {
            Events::where('id', $validatedData['event_id'])->update([
                'title' => $validatedData['edit_title'],
            ]);
        } catch (\Exception $e) {
            return redirect('/admin/events')->with('error', 'Error updating Event : '.$e->getMessage());
        }

        return redirect('/admin/events')->with('success', 'Event is updated successfully!');
    }

    public function delete_event(string $event_id)
    {
        DB::transaction(function () use ($event_id) {
            $event = Events::findOrFail($event_id);
            $userIds = $event->candidates()->pluck('user_id');
            $photoPaths = DB::table('challenge_photos')
                ->join('challenges', 'challenge_photos.challenge_id', '=', 'challenges.id')
                ->where('challenges.event_id', $event_id)
                ->pluck('challenge_photos.photo_path')->all();

            $event->delete();

            User::whereIn('id', $userIds)
                ->where('user_type', 'user')
                ->where('id', '!=', auth()->id())
                ->whereDoesntHave('candidates')
                ->delete();

            DB::afterCommit(function () use ($photoPaths) {
                Storage::disk('public')->delete($photoPaths);
            });
        });

        return redirect('/admin/events')->with('success', 'Event, related data, and associated contestant accounts deleted successfully. Accounts used by other events were preserved.');
    }

    public function add_candidate(Request $request)
    {
        $validatedData = $request->validate([
            'event_id' => 'required|exists:events,id',
            'candidate_name' => 'required|string|max:255',
            'username' => 'required|string|max:128|unique:users,name',
            'user_password' => 'required|string|min:4',
        ]);

        try {
            DB::transaction(function () use ($validatedData) {
                $user = User::create([
                    'name' => trim($validatedData['username']),
                    'email' => trim(strtolower($validatedData['username'])).'@hungergame.com',
                    'password' => bcrypt($validatedData['user_password']),
                    'user_type' => 'user',
                ]);

                Candidates::create([
                    'event_id' => $validatedData['event_id'],
                    'fullname' => $validatedData['candidate_name'],
                    'group' => 'none',
                    'user_id' => $user->id,
                ]);
            });
        } catch (\Exception $e) {
            return redirect('/admin/candidates')->with('error', 'Error creating competitor: '.$e->getMessage());
        }

        return redirect('/admin/candidates')->with('success', 'A Competitor is added!');
    }

    public function update_candidate(Request $request)
    {
        $request->validate(['candidate_id' => 'required|exists:candidates,id']);
        $candidate = Candidates::findOrFail($request->input('candidate_id'));

        $validatedData = $request->validate([
            'event_id' => 'required|exists:events,id',
            'candidate_id' => 'required|exists:candidates,id',
            'edit_name' => 'required|string|max:255',
            'edit_username' => ['required', 'string', 'max:128', Rule::unique('users', 'name')->ignore($candidate->user_id)],
            'edit_user_password' => 'nullable|string|min:4',
        ]);

        try {
            DB::transaction(function () use ($validatedData) {
                $candidate = Candidates::whereKey($validatedData['candidate_id'])->lockForUpdate()->firstOrFail();
                if ((int) $candidate->event_id !== (int) $validatedData['event_id'] && $candidate->submissions()->exists()) {
                    throw ValidationException::withMessages([
                        'event_id' => 'A competitor with submissions cannot be moved to another event.',
                    ]);
                }
                $user = $candidate->user;

                $user->update([
                    'name' => trim($validatedData['edit_username']),
                    'email' => trim(strtolower($validatedData['edit_username'])).'@hungergame.com',
                ]);

                if (! empty($validatedData['edit_user_password'])) {
                    $user->update([
                        'password' => bcrypt($validatedData['edit_user_password']),
                    ]);
                }

                $candidate->update([
                    'event_id' => $validatedData['event_id'],
                    'fullname' => $validatedData['edit_name'],
                ]);
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect('/admin/candidates')->with('error', 'Error updating competitor: '.$e->getMessage());
        }

        return redirect('/admin/candidates')->with('success', 'Competitor updated successfully!');
    }

    public function view_candidate(string $candidate_id)
    {
        $candidate = Candidates::with('user')->where('id', $candidate_id)->firstOrFail();

        $solved_challenges = Challenges::select('submissions.id', 'challenges.title', 'challenges.points', 'challenges.language', 'challenges.difficulty', 'challenges.created_at', 'submissions.challenge_id', 'submissions.score')
            ->join('submissions', function ($join) use ($candidate) {
                $join->on('submissions.challenge_id', '=', 'challenges.id')
                    ->where('submissions.candidate_id', '=', $candidate->id);
            })
            ->get();

        $candidates = Candidates::with('user')->latest()->get();
        $events = Events::all();

        return view('admin.candidates', compact('candidates', 'solved_challenges', 'events', 'candidate'));
    }

    public function get_candidate_codes(string $id)
    {
        $code_solutions = Submissions::where('submissions.id', $id)
            ->join('challenges', 'submissions.challenge_id', '=', 'challenges.id')
            ->select('submissions.code', 'challenges.code_solution', 'challenges.description')
            ->first();

        return response()->json($code_solutions);
    }

    public function delete_candidate(string $candidate_id)
    {
        DB::transaction(function () use ($candidate_id) {
            $candidate = Candidates::whereKey($candidate_id)->lockForUpdate()->firstOrFail();
            $user = User::whereKey($candidate->user_id)->lockForUpdate()->first();

            $candidate->delete();

            if ($user) {
                User::whereKey($user->id)
                    ->where('user_type', 'user')
                    ->where('id', '!=', auth()->id())
                    ->whereDoesntHave('candidates')
                    ->delete();
            }
        });

        return redirect('/admin/candidates')->with('success', 'Candidate deleted successfully. Unused contestant account removed if eligible.');
    }

    public function challenges()
    {
        $event = Events::where('isActive', true)->first();
        $challenges = collect();
        $events = Events::all();
        $languages = ProgrammingLanguage::where('is_active', true)->get();

        if ($event) {
            $challenges = Challenges::where('event_id', $event->id)
                ->with('photos', 'testCases')
                ->latest()->get();
        }

        $draftPhotos = Challenges::find(session()->getOldInput('challenge_id'))?->photos ?? collect();

        return view('admin.challenges', compact('challenges', 'events', 'languages', 'draftPhotos'));
    }

    public function get_challenge_details($id)
    {
        $challenge = Challenges::with('photos', 'testCases')->findOrFail($id);

        $challenge->photos_list = $challenge->photos->map(function ($photo) {
            return [
                'id' => $photo->id,
                'url' => $photo->photo_path ? asset('storage/'.$photo->photo_path) : null,
                'path' => $photo->photo_path,
            ];
        });

        $challenge->test_cases_list = $challenge->testCases->map(function ($tc) {
            return [
                'id' => $tc->id,
                'input' => $tc->input,
                'expected_output' => $tc->expected_output,
                'points' => $tc->points,
                'is_active' => $tc->is_active,
            ];
        });

        return response()->json($challenge);
    }

    public function save_challenge(Request $request)
    {
        $validatedData = $request->validate([
            'event_id' => 'required|exists:events,id',
            'challenge_id' => 'nullable|integer|min:1',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'difficulty' => 'required|in:easy,medium,hard,challenge',
            'language' => 'required|string|max:50',
            'language_id' => 'nullable|exists:programming_languages,id',
            'points' => 'required|integer|min:1',
            'code_solution' => 'required|string',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'delete_photos' => 'nullable|array',
            'delete_photos.*' => 'integer|exists:challenge_photos,id',
            'test_cases_present' => 'sometimes|boolean',
            'test_cases' => 'nullable|array',
            'test_cases.*.input' => 'nullable|string',
            'test_cases.*.expected_output' => 'required_with:test_cases|string',
            'test_cases.*.points' => 'required_with:test_cases|numeric|min:0',
        ]);

        $language = ProgrammingLanguage::where('is_active', true)->find($validatedData['language_id'] ?? null);
        if (! $language || $language->name !== $validatedData['language']) {
            throw ValidationException::withMessages(['language_id' => 'Select a matching active programming language.']);
        }

        $replaceTestCases = $request->boolean('test_cases_present') || $request->has('test_cases');
        $testCasePoints = $replaceTestCases
            ? collect($validatedData['test_cases'] ?? [])->sum('points')
            : Challenges::find($validatedData['challenge_id'] ?? null)?->testCases()->sum('points') ?? 0;
        if ($testCasePoints > $validatedData['points']) {
            throw ValidationException::withMessages(['test_cases' => 'Test case points cannot exceed challenge points.']);
        }

        $uploadedPaths = [];
        $deletedPaths = [];
        try {
            $challenge = DB::transaction(function () use ($request, $validatedData, $replaceTestCases, &$uploadedPaths, &$deletedPaths) {
                $challenge = ! empty($validatedData['challenge_id'])
                    ? Challenges::lockForUpdate()->findOrFail($validatedData['challenge_id'])
                    : new Challenges;
                if ($challenge->exists && $challenge->submissions()->exists()) {
                    throw ValidationException::withMessages([
                        'challenge_id' => 'Challenges with submissions cannot be edited. Create a new challenge to change the task or grading rubric.',
                    ]);
                }
                $challenge->fill([
                    'event_id' => $validatedData['event_id'],
                    'title' => $validatedData['title'],
                    'description' => $validatedData['description'],
                    'difficulty' => $validatedData['difficulty'],
                    'language' => $validatedData['language'],
                    'language_id' => $validatedData['language_id'],
                    'points' => $validatedData['points'],
                    'code_solution' => $validatedData['code_solution'],
                ]);
                $challenge->save();

                if (! empty($validatedData['delete_photos'])) {
                    $photosToDelete = $challenge->photos()->whereIn('id', $validatedData['delete_photos'])->get();
                    foreach ($photosToDelete as $photo) {
                        $deletedPaths[] = $photo->photo_path;
                        $photo->delete();
                    }
                }

                foreach ($request->file('photos', []) as $photoFile) {
                    $path = $photoFile->store('challenge-photos', 'public');
                    if (! $path) {
                        throw new \RuntimeException('Unable to store challenge photo.');
                    }
                    $uploadedPaths[] = $path;
                    $challenge->photos()->create(['photo_path' => $path]);
                }

                if ($replaceTestCases) {
                    $challenge->testCases()->delete();
                    foreach ($validatedData['test_cases'] ?? [] as $tc) {
                        $challenge->testCases()->create([
                            'input' => $tc['input'] ?? '',
                            'expected_output' => $tc['expected_output'],
                            'points' => $tc['points'],
                            'is_active' => true,
                        ]);
                    }
                }

                return $challenge;
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($uploadedPaths);
            Log::error('Failed to save challenge', ['exception' => $e]);

            return back()->withInput()->with('error', 'Something went wrong while saving the challenge.');
        }

        // Keep existing files until their database changes are committed.
        Storage::disk('public')->delete($deletedPaths);

        return redirect('/admin/challenges')->with(
            'success',
            $challenge->wasRecentlyCreated
                ? 'Challenge created successfully!'
                : 'Challenge updated successfully!'
        );
    }

    public function delete_challenge(string $id)
    {
        $photoPaths = DB::transaction(function () use ($id) {
            $challenge = Challenges::with('photos')->findOrFail($id);
            $photoPaths = $challenge->photos->pluck('photo_path')->all();
            $challenge->delete();

            return $photoPaths;
        });
        Storage::disk('public')->delete($photoPaths);

        return redirect('/admin/challenges')->with('success', 'A Challenge is deleted successfully.');
    }

    public function toggle_registration()
    {
        $current = DB::table('options')
            ->where('option_name', 'registration_open')
            ->value('option_value');

        $newValue = $current === '1' ? '0' : '1';

        DB::table('options')->updateOrInsert(
            ['option_name' => 'registration_open'],
            ['option_value' => $newValue]
        );

        $status = $newValue === '1' ? 'opened' : 'closed';

        return redirect('/admin/dashboard')->with('success', "Registration is now {$status}.");
    }
}
