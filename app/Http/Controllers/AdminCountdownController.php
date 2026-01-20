<?php

namespace App\Http\Controllers;

use App\Events\CountdownEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Carbon\Carbon;

use Illuminate\Support\Facades\DB;

class AdminCountdownController extends Controller
{
    public function start(Request $request)
    {
        // Check cache first if we have a paused countdown
        $durationMs = Cache::has('competition_remaining_ms')
            ? Cache::get('competition_remaining_ms')
            : $request->integer('duration_ms');

        if ($durationMs <= 0) {
            return response()->json(['error' => 'Invalid duration'], 422);
        }

        $endTime = Carbon::now()->addMilliseconds($durationMs);
        $endTimeInMs = $endTime->timestamp * 1000;

        Cache::put('competition_end_time', $endTimeInMs);
        Cache::forget('competition_remaining_ms');

        if (Cache::has('competition_remaining_ms')) {
            broadcast(new CountdownEvent("resume", ["endTime" => $endTimeInMs]));
        } else {
            broadcast(new CountdownEvent("start", ["endTime" => $endTimeInMs]));
        }

        // start the compition
        DB::table('options')
            ->where('option_name', "start_compition")
            ->update(['option_value' => '1']);

        return response()->json([
            'endTime' => $endTime->timestamp * 1000,
        ]);
    }

    public function pause()
    {
        $endTime = Cache::get('competition_end_time');

        if (!$endTime) {
            return response()->json(['status' => 'not_running']);
        }

        $remaining = max(0, $endTime - now()->timestamp * 1000);

        Cache::forget('competition_end_time');
        Cache::put('competition_remaining_ms', $remaining);

        broadcast(new CountdownEvent("paused", ["remainingMs" => $remaining]));

        return response()->json([
            'remainingMs' => $remaining,
        ]);
    }

    public function reset()
    {
        Cache::forget('competition_end_time');
        Cache::forget('competition_remaining_ms');

        // stop the compition
        DB::table('options')
            ->where('option_name', "start_compition")
            ->update(['option_value' => '0']);

        broadcast(new CountdownEvent("reset"));
        return response()->json(['status' => 'reset']);
    }
}
