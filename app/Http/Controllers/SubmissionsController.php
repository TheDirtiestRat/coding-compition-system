<?php

namespace App\Http\Controllers;

use App\Models\Submissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubmissionsController extends Controller
{
    public function show($id)
    {
        $submission = Submissions::where('submissions.id', $id)
            ->join('challenges', 'submissions.challenge_id', '=', 'challenges.id')
            ->join('candidates', 'submissions.candidate_id', '=', 'candidates.id')
            ->select(
                'submissions.id',
                'submissions.updated_at as submitted_at',
                'submissions.code',
                'submissions.is_score_submitted',
                'submissions.is_auto_reviewed',
                'submissions.test_case_results',
                'candidates.fullname as candidate_name',
                'challenges.title as challenge_title',
                'challenges.description as challenge_description',
                'challenges.points as max_points',
                'challenges.difficulty as challenge_difficulty',
                'challenges.language as challenge_language',
                'challenges.language_id as challenge_language_id',
                'challenges.code_solution',
                'challenges.description',
            )
            ->first();

        if ($submission) {
            $submission->code_hash = hash('sha256', $submission->code);
        }

        return response()->json(['submission' => $submission]);
    }

    public function submit_score(Request $request, $id)
    {
        $submission = Submissions::with('challenge')->findOrFail($id);

        if ($submission->is_score_submitted) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Score has already been submitted for this submission.'], 409);
            }

            return redirect()
                ->back()
                ->with('error', 'Score has already been submitted for this submission.');
        }

        $maxPoints = $submission->challenge->points;

        $validated = $request->validate([
            'score' => ['required', 'integer', 'min:0', 'max:' . $maxPoints],
            'code_hash' => ['required', 'string', 'size:64'],
        ]);

        try {
            $updated = DB::transaction(function () use ($id, $validated) {
                $submission = Submissions::whereKey($id)->lockForUpdate()->first();

                if (!$submission || $submission->is_score_submitted
                    || !hash_equals(hash('sha256', $submission->code), $validated['code_hash'])) {
                    return false;
                }

                return Submissions::whereKey($id)
                    ->where('is_score_submitted', false)
                    ->update([
                        'score' => $validated['score'],
                        'is_score_submitted' => true,
                    ]);
            });

            if (!$updated) {
                return response()->json(['message' => 'Submission changed or was already reviewed. Reload it before scoring.'], 409);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to submit score', [
                'submission_id' => $id,
                'payload' => $validated,
                'exception' => $e,
            ]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Something went wrong while saving the score.'], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong while saving the score.');
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Score submitted successfully!']);
        }

        return redirect('/admin/submissions')
            ->with('success', 'Score submitted successfully!');
    }
}
