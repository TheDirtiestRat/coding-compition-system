<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CodeCheckerController;
use App\Http\Controllers\CompilerController;

Route::get('/', function () {
    return view('pages.home');
});

Route::get('/home', function () {
    return view('pages.home');
});

Route::get('/login', function () {
    return view('authentication.login');
});

Route::get('/code-editor', function () {
    return view('pages.code-editor');
});

Route::get('/code-problems', function () {
    return view('pages.code-problems');
});

// to run server side code on my server device

Route::post('/verify-code-challenge', [CodeCheckerController::class, 'checkCodedText']);

Route::post('/run-python-code', [CompilerController::class, 'run_python_code'])->middleware('web');

Route::post('/run-java-code', [CompilerController::class, 'run_java_code'])->middleware('web');
