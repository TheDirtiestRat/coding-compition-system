<?php

use App\Http\Controllers\AdministratorController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CodeCheckerController;
use App\Http\Controllers\CompilerController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\AdminCountdownController;
use App\Http\Controllers\CountdownController;
use App\Http\Controllers\SubmissionsController;
use Illuminate\Support\Facades\DB;


Route::get('/', function () {
    return view('pages.home');
});
Route::get('/home', function () {
    return view('pages.home');
});

// Authentication Routes
Route::get('/login', [AuthenticationController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthenticationController::class, 'loginUser']);
Route::post('/logout', [AuthenticationController::class, 'logout'])->name('logout');

// Countdown
Route::get('/countdown', [CountdownController::class, 'show']);

// admin side pages
Route::middleware(['auth', 'is.admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/admin/candidates', [AdminController::class, 'candidates']);
    Route::get('/admin/challenges', [AdminController::class, 'challenges']);
    Route::get('/admin/submissions', [AdminController::class, 'submissions']);

    Route::post('/admin/add-candidate', [AdminController::class, 'add_candidate']);
    Route::post('/admin/update-candidate', [AdminController::class, 'update_candidate']);
    Route::delete('/admin/delete-candidate/{candidate_id}', [AdminController::class, 'delete_candidate']);

    Route::get('/admin/api/get-challenge-details/{id}', [AdminController::class, 'get_challenge_details']);
    Route::post('/admin/save-challenge', [AdminController::class, 'save_challenge']);
    Route::delete('/admin/delete-challenge/{id}', [AdminController::class, 'delete_challenge']);

    Route::post('/admin/countdown/start', [AdminCountdownController::class, 'start']);
    Route::post('/admin/countdown/pause', [AdminCountdownController::class, 'pause']);
    Route::post('/admin/countdown/reset', [AdminCountdownController::class, 'reset']);

    Route::get('/admin/api/submissions/{id}', [SubmissionsController::class, 'show']);

    // the counting down page
    Route::get('/admin/countdown', function () {
        return view('pages.countdown');
    });
});

// user side pages
Route::middleware('auth')->group(function () {

    Route::get('/code-problems', [CandidateController::class, 'code_problems']);

    Route::middleware(['timer_stop'])->group(function () {
        Route::get('/code-editor', [CandidateController::class, 'open_code_editor']);
        Route::get('/code-editor/{challenge_id}', [CandidateController::class, 'open_code_editor']);

        Route::post('/api/submit-code-for-review', [CandidateController::class, 'submit_code_for_review']);

        # Deprecated // Route::post('/api/verify-code-challenge', [CandidateController::class, 'verifySubmittedCode']);
        Route::get('/api/get-challenge-details/{id}', [CandidateController::class, 'get_challenge_details']);
        // to run server side code on my server device
        // Route::post('/api/verify-code-challenge', [CodeCheckerController::class, 'checkCodedText']);
        Route::post('/run-python-code', [CompilerController::class, 'run_python_code'])->middleware('web');
        Route::post('/run-java-code', [CompilerController::class, 'run_java_code'])->middleware('web');
    });

    // when the count down is finished the end screen will appear
    Route::get('/competition-ended', function () {
        return view('pages.competition-ended');
    });
});
