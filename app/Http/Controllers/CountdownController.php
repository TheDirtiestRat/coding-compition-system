<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;

class CountdownController extends Controller
{
    public function show()
    {
        return response()->json([
            'endTime' => Cache::get('competition_end_time'),
            'remainingMs' => Cache::get('competition_remaining_ms'),
        ]);
    }
}
