<?php

namespace App\Http\Controllers;

use App\Support\CompetitionTimer;

class CountdownController extends Controller
{
    public function show()
    {
        return response()->json(CompetitionTimer::state());
    }
}
