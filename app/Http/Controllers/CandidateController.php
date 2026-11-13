<?php

namespace App\Http\Controllers;

use App\Models\Challenges;
use App\Models\Candidates;
use App\Models\Events;
use App\Models\Submissions;
use App\Models\ProgrammingLanguage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Illuminate\Support\Str;

class CandidateController extends Controller
{
    public function get_all_challenges()
    {
        $challenges = Challenges::latest()->get();

        return response()->json($challenges);
    }

    public function get_challenge_details($id)
    {
        $candidate = Candidates::where('user_id', Auth::id())->firstOrFail();
        $challenge = Challenges::with('photos')
            ->select(['id', 'title', 'description', 'difficulty', 'points'])
            ->where('event_id', $candidate->event_id)
            ->findOrFail($id);

        $challenge->photos_list = $challenge->photos->map(function ($photo) {
            return [
                'id' => $photo->id,
                'url' => $photo->photo_path ? asset('storage/' . $photo->photo_path) : null,
            ];
        });

        return response()->json($challenge);
    }

    public function code_problems()
    {
        $user = Auth::user();
        $candidate = Candidates::where('user_id', $user->id)->first();

        if (!$candidate) {
            return view('pages.code-problems', [
                'challenges' => collect(),
                'easy_challenges' => collect(),
                'medium_challenges' => collect(),
                'hard_challenges' => collect(),
                'unsolved_challenge_id' => null,
                'competition_started' => false,
            ]);
        }

        $baseQuery = fn($difficulty) =>
            Challenges::where('challenges.difficulty', $difficulty)->where('challenges.event_id', $candidate->event_id)
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

        $unsolved_challenge_id = Challenges::query()
            ->leftJoin('submissions', function ($join) use ($candidate) {
                $join->on('submissions.challenge_id', '=', 'challenges.id')
                    ->where('submissions.candidate_id', '=', $candidate->id);
            })
            ->whereNull('submissions.id')
            ->where('challenges.event_id', $candidate->event_id)
            ->orderByRaw("
                CASE challenges.difficulty
                    WHEN 'easy' THEN 1
                    WHEN 'medium' THEN 2
                    WHEN 'hard' THEN 3
                    WHEN 'challenge' THEN 4
                    ELSE 4
                END
            ")
            ->orderBy('challenges.id', 'asc')
            ->value('challenges.id');

        $competition_started = DB::table('options')
            ->where('option_name', 'start_compition')
            ->value('option_value') === '1';

        return view('pages.code-problems', compact(
            'challenges',
            'easy_challenges',
            'medium_challenges',
            'hard_challenges',
            'unsolved_challenge_id',
            'competition_started'
        ));
    }

    public function open_code_editor(string $challenge_id = "-1")
    {
        $user = Auth::user();
        $candidate = Candidates::where('user_id', $user->id)->first();

        if (!$candidate) {
            return redirect('/code-problems');
        }

        $event = Events::where('isActive', true)->first();

        if (!$event) {
            return redirect('/code-problems');
        }

        if ($challenge_id >= 0) {
            $details = Challenges::with('photos')
                ->where('challenges.event_id', $candidate->event_id)
                ->leftJoin('submissions', function ($join) use ($candidate) {
                    $join->on('submissions.challenge_id', '=', 'challenges.id')
                        ->where('submissions.candidate_id', '=', $candidate->id);
                })
                ->selectRaw("
                        challenges.*,
                        submissions.score,
                        submissions.code,
                        submissions.test_case_results,
                        CASE
                            WHEN submissions.is_score_submitted = 1 THEN 'reviewed'
                            WHEN submissions.id IS NOT NULL THEN 'submitted'
                            ELSE 'not_started'
                        END AS status
                    ")
                ->where('challenges.id', $challenge_id)
                ->first();

            if (!$details) {
                return redirect('/code-problems');
            }

            $next_id = Challenges::where('id', '>', $challenge_id)
                ->where('event_id', $candidate->event_id)
                ->orderBy('id', 'asc')
                ->first('id');

            $next_challenge_id = $next_id?->id;

            return view('pages.code-editor', compact('details', 'next_challenge_id'));
        }

        return view('pages.code-editor');
    }

    public function submit_code_for_review(Request $request)
    {
        $user = Auth::user();
        $candidate = Candidates::where('user_id', $user->id)->first();

        $request->validate([
            'challenge_id' => 'required|integer',
            'code' => 'required|string',
        ]);

        $challenge_id = $request->input('challenge_id');

        if (!$candidate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Candidate not found.'
            ], 404);
        }

        $challenge = Challenges::with('activeTestCases', 'languageModel')
            ->where('event_id', $candidate->event_id)
            ->find($challenge_id);

        if (!$challenge) {
            return response()->json([
                'status' => 'error',
                'message' => 'Challenge not found.'
            ], 404);
        }

        $existingSubmission = Submissions::where('candidate_id', $candidate->id)
            ->where('challenge_id', $challenge_id)
            ->first();

        if ($existingSubmission?->is_score_submitted) {
            return response()->json([
                'status' => 'error',
                'message' => 'This submission has already been reviewed and can no longer be modified.',
            ], 409);
        }

        $code = $request->input('code');

        // Auto-check test cases
        $testCases = $challenge->activeTestCases;
        $testCaseResults = [];
        $allPassed = $testCases->isNotEmpty() && $challenge->languageModel !== null;
        $totalAutoScore = 0;

        if ($testCases->isNotEmpty() && $challenge->languageModel) {
            $language = $challenge->languageModel;
            $executionTimeout = 5;

            foreach ($testCases as $index => $tc) {
                $result = [
                    'test_case_id' => $tc->id,
                    'input' => $tc->input,
                    'expected_output' => $tc->expected_output,
                    'points' => $tc->points,
                    'passed' => false,
                    'actual_output' => null,
                    'error' => null,
                ];

                try {
                    $output = $this->runCodeWithInput($code, $language, $tc->input, $executionTimeout);
                    $result['actual_output'] = $output;

                    if (trim($output) === trim($tc->expected_output)) {
                        $result['passed'] = true;
                        $totalAutoScore += $tc->points;
                    }
                } catch (\Throwable $e) {
                    $result['error'] = $e->getMessage();
                    $allPassed = false;
                }

                $testCaseResults[] = $result;
                if (!$result['passed']) {
                    $allPassed = false;
                }
            }
        }

        $submissionData = [
            'candidate_id' => $candidate->id,
            'challenge_id' => $challenge_id,
            'code' => $code,
            'test_case_results' => $testCaseResults,
        ];

        $totalAutoScore = min($totalAutoScore, $challenge->points);

        // If all test cases passed, auto-review
        if ($testCases->isNotEmpty() && $allPassed) {
            $submissionData['score'] = $totalAutoScore;
            $submissionData['is_score_submitted'] = true;
            $submissionData['is_auto_reviewed'] = true;
        } else {
            $submissionData['score'] = 0;
            $submissionData['is_score_submitted'] = false;
            $submissionData['is_auto_reviewed'] = false;
        }

        DB::transaction(function () use ($candidate, $challenge_id, $submissionData) {
            Candidates::whereKey($candidate->id)->lockForUpdate()->firstOrFail();

            $submission = Submissions::where('candidate_id', $candidate->id)
                ->where('challenge_id', $challenge_id)
                ->lockForUpdate()
                ->first();

            abort_if($submission?->is_score_submitted, 409, 'This submission has already been reviewed.');

            Submissions::updateOrCreate(
                ['candidate_id' => $candidate->id, 'challenge_id' => $challenge_id],
                $submissionData
            );
        });

        return response()->json([
            'status' => 'success',
            'message' => $allPassed && $testCases->isNotEmpty()
                ? 'All test cases passed! Score: ' . $totalAutoScore
                : 'Code submitted successfully. Awaiting review...',
            'auto_reviewed' => $allPassed && $testCases->isNotEmpty(),
            'test_case_results' => $testCaseResults,
        ], 200);
    }

    private function runCodeWithInput($code, $language, $input, $timeout = 5)
    {
        $uniqueId = Str::random(10);
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'auto_test_' . $uniqueId;
        File::makeDirectory($tempDir);

        $className = 'Main';
        $sourceFile = $tempDir . DIRECTORY_SEPARATOR . $className . $language->file_extension;
        File::put($sourceFile, $code);

        try {
            if ($language->compile_command) {
                $compileCmd = str_replace('{file}', $sourceFile, $language->compile_command);
                $compileProcess = Process::fromShellCommandline($compileCmd, $tempDir);
                $compileProcess->setTimeout($timeout);
                $compileProcess->run();

                if (!$compileProcess->isSuccessful()) {
                    throw new \RuntimeException("Compilation Error:\n" . $compileProcess->getErrorOutput());
                }
            }

            $filename = $className . $language->file_extension;
            $runCmd = str_replace('{file}', $filename, $language->run_command);
            $runCmd = str_replace('{dir}', $tempDir, $runCmd);
            $runProcess = Process::fromShellCommandline($runCmd, $tempDir);
            $runProcess->setTimeout($timeout);
            if ($input) {
                $runProcess->setInput($input);
            }
            $runProcess->run();

            if (!$runProcess->isSuccessful()) {
                throw new \RuntimeException($runProcess->getErrorOutput() ?: 'Execution error');
            }

            return $runProcess->getOutput();
        } catch (ProcessTimedOutException $e) {
            throw new \RuntimeException("Process timed out (Max {$timeout}s)");
        } finally {
            File::deleteDirectory($tempDir);
        }
    }
}
