<?php

namespace App\Http\Controllers;

use App\Models\Challenges;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    public function get_all_challenges()
    {
        $challenges = Challenges::latest()->get();

        return  response()->json($challenges);
    }

    /**
     * Display a listing of the resource.
     */
    public function code_problems()
    {
        $easy_challenges = Challenges::where("difficulty", "=", 'easy')->latest()->get();
        $medium_challenges = Challenges::where("difficulty", "=", 'medium')->latest()->get();
        $hard_challenges = Challenges::where("difficulty", "=", 'hard')->latest()->get();
        $challenges = Challenges::where("difficulty", "=", 'challenge')->latest()->get();

        return view('pages.code-problems', compact('challenges','easy_challenges','medium_challenges','hard_challenges'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function open_code_editor()
    {
        return view('pages.code-editor');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        //
    }
}
