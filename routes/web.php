<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\CompilerController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\AdminCountdownController;
use App\Http\Controllers\CountdownController;
use App\Http\Controllers\SubmissionsController;
use App\Http\Controllers\ProgrammingLanguageController;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


Route::get('/', function () {
    return view('pages.home');
});

Route::get('/home', function () {
    return view('pages.home');
});

Route::get('/credits', function () {
    return view('pages.credits');
});

// Authentication Routes
Route::get('/login', [AuthenticationController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthenticationController::class, 'loginUser']);
Route::post('/logout', [AuthenticationController::class, 'logout'])->name('logout');

// Registration Routes
Route::get('/register', [AuthenticationController::class, 'showRegistrationForm']);
Route::post('/register', [AuthenticationController::class, 'register']);

// Countdown
Route::get('/countdown', [CountdownController::class, 'show']);


// user side pages
Route::middleware('auth')->group(function () {

    // admin side pages
    Route::middleware('is.admin')->group(function () {
        Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/admin/results', [AdminController::class, 'results']);
        Route::get('/admin/events', [AdminController::class, 'events']);
        Route::get('/admin/candidates', [AdminController::class, 'candidates']);
        Route::get('/admin/challenges', [AdminController::class, 'challenges']);
        Route::get('/admin/submissions', [AdminController::class, 'submissions']);
        Route::get('/admin/programming-languages', [ProgrammingLanguageController::class, 'index']);

        Route::post('/admin/add-event', [AdminController::class, 'add_event']);
        Route::post('/admin/update-event', [AdminController::class, 'update_event']);
        Route::post('/admin/active-event', [AdminController::class, 'activate_event']);
        Route::delete('/admin/delete-event/{event_id}', [AdminController::class, 'delete_event']);

        Route::get('/admin/candidates/view/{candidate_id}', [AdminController::class, 'view_candidate']);
        Route::post('/admin/add-candidate', [AdminController::class, 'add_candidate']);
        Route::post('/admin/update-candidate', [AdminController::class, 'update_candidate']);
        Route::delete('/admin/delete-candidate/{candidate_id}', [AdminController::class, 'delete_candidate']);
        Route::get('/admin/get-candidates-codes/{id}', [AdminController::class, 'get_candidate_codes']);

        Route::get('/admin/api/get-challenge-details/{id}', [AdminController::class, 'get_challenge_details']);
        Route::post('/admin/save-challenge', [AdminController::class, 'save_challenge']);
        Route::delete('/admin/delete-challenge/{id}', [AdminController::class, 'delete_challenge']);

        Route::post('/admin/countdown/start', [AdminCountdownController::class, 'start']);
        Route::post('/admin/countdown/pause', [AdminCountdownController::class, 'pause']);
        Route::post('/admin/countdown/reset', [AdminCountdownController::class, 'reset']);

        Route::post('/admin/timer/set_timer', [AdminCountdownController::class, 'set_timer']);
        Route::post('/admin/timer/get_timer', [AdminCountdownController::class, 'get_timer']);

        Route::get('/admin/api/submissions_list', [AdminController::class, 'submissions_api']);
        Route::get('/admin/api/submissions/{id}', [SubmissionsController::class, 'show']);
        Route::post('/admin/api/submissions/{id}/submit-score', [SubmissionsController::class, 'submit_score']);

        Route::get('/admin/api/get-languages', [ProgrammingLanguageController::class, 'api_list']);
        Route::post('/admin/programming-languages/store', [ProgrammingLanguageController::class, 'store']);
        Route::post('/admin/programming-languages/update', [ProgrammingLanguageController::class, 'update']);
        Route::delete('/admin/programming-languages/delete/{id}', [ProgrammingLanguageController::class, 'destroy']);

        Route::post('/admin/toggle-registration', [AdminController::class, 'toggle_registration']);

        Route::get('/admin/countdown', function () {
            return view('pages.countdown');
        });
    });

    Route::middleware(['timer_stop'])->group(function () {
        Route::get('/code-editor', [CandidateController::class, 'open_code_editor']);
        Route::get('/code-editor/{challenge_id}', [CandidateController::class, 'open_code_editor']);

        Route::post('/api/submit-code-for-review', [CandidateController::class, 'submit_code_for_review']);
    });

    Route::get('/code-problems', [CandidateController::class, 'code_problems']);

    Route::get('/api/get-challenge-details/{id}', [CandidateController::class, 'get_challenge_details']);
    Route::get('/api/get-languages', [ProgrammingLanguageController::class, 'api_list']);

    Route::post('/run-java-code', [CompilerController::class, 'run_java_code_unique']);
    Route::post('/run-python-code', [CompilerController::class, 'run_python_code']);
    Route::post('/run-code', [CompilerController::class, 'run_code_with_language']);

    Route::post('/timer/get_timer', [AdminCountdownController::class, 'get_timer']);

    Route::get('/competition-ended', function () {
        $started = DB::table('options')
            ->where('option_name', 'start_compition')
            ->value('option_value');

        if ($started !== '1') {
            return redirect('/code-problems');
        }

        $end_time = DB::table('options')
            ->where('option_name', 'expected_end_time')
            ->value('option_value');

        if (!$end_time || Carbon::now()->timestamp < Carbon::parse($end_time)->timestamp) {
            return redirect('/code-problems');
        }

        $user = Auth::user();
        $candidate = \App\Models\Candidates::where('user_id', $user?->id)->first();
        $totalScore = 0;

        if ($candidate) {
            $totalScore = \App\Models\Submissions::where('candidate_id', $candidate->id)
                ->sum('score');
        }

        return view('pages.competition-ended', compact('totalScore'));
    });
});
