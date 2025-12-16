<?php

use App\Http\Controllers\AdministratorController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CodeCheckerController;
use App\Http\Controllers\CompilerController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CandidateController;

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

// admin side pages
Route::middleware(['auth', 'is.admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/admin/candidates', [AdminController::class, 'candidates']);
    Route::get('/admin/challenges', [AdminController::class, 'challenges']);

    Route::post('/admin/add-candidate', [AdminController::class, 'add_candidate']);
    Route::post('/admin/update-candidate', [AdminController::class, 'update_candidate']);
    Route::delete('/admin/delete-candidate/{candidate_id}', [AdminController::class, 'delete_candidate']);

    Route::get('/admin/api/get-challenge-details/{id}', [AdminController::class, 'get_challenge_details']);
    Route::post('/admin/create-challenge', [AdminController::class, 'create_challenge']);
    Route::post('/admin/update-challenge', [AdminController::class, 'update_challenge']);
    Route::delete('/admin/delete-challenge/{id}', [AdminController::class, 'delete_challenge']);
});

// user side pages
Route::middleware('auth')->group(function () {
    Route::get('/code-editor', [CandidateController::class, 'open_code_editor']);
    Route::get('/code-problems', [CandidateController::class, 'code_problems']);

    // to run server side code on my server device
    Route::post('/verify-code-challenge', [CodeCheckerController::class, 'checkCodedText']);
    Route::post('/run-python-code', [CompilerController::class, 'run_python_code'])->middleware('web');
    Route::post('/run-java-code', [CompilerController::class, 'run_java_code'])->middleware('web');
});
