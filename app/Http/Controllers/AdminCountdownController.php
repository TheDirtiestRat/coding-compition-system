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

        $eventType = Cache::has('competition_remaining_ms') ? 'resume' : 'start';

        broadcast(new CountdownEvent($eventType, ["endTime" => $endTimeInMs]));

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

        DB::table('options')
            ->where('option_name', "start_compition")
            ->update(['option_value' => '0']);

        DB::table('options')
            ->where('option_name', "expected_end_time")
            ->update(['option_value' => '00:00:00']);

        broadcast(new CountdownEvent("reset"));
        return response()->json(['status' => 'reset']);
    }

    public function set_timer(Request $request)
    {
        $validatedData = $request->validate([
            'setHours' => 'required|integer|min:0',
            'setMinutes' => 'required|integer|min:0|max:59',
            'setSeconds' => 'required|integer|min:0|max:59',
        ]);

        $current_time = Carbon::now();

        $hours = (int) $validatedData['setHours'];
        $minutes = (int) $validatedData['setMinutes'];
        $seconds = (int) $validatedData['setSeconds'];

        $end_time = $current_time->copy()->addHours($hours)->addMinutes($minutes)->addSeconds($seconds);

        DB::table('options')
            ->where('option_name', "set_time")
            ->update(['option_value' => $current_time->format('H:i:s')]);

        DB::table('options')
            ->where('option_name', "expected_end_time")
            ->update(['option_value' => $end_time->format('H:i:s')]);

        DB::table('options')
            ->where('option_name', "start_compition")
            ->update(['option_value' => '1']);

        broadcast(new CountdownEvent("start", ["endTime" => $end_time->timestamp * 1000]));

        return response()->json([
            'current_time' => $current_time->format('H:i:s'),
            'formatted_set_time' => $current_time->format('H:i:s'),
            'formatted_end_time' => $end_time->format('H:i:s'),
        ]);
    }

    public function get_timer()
    {
        $current_time = Carbon::now();
        $curTimeInMils = $current_time->timestamp;

        $started = DB::table('options')
            ->where('option_name', "start_compition")->value('option_value');

        $get_end_time = DB::table('options')
            ->where('option_name', "expected_end_time")->value('option_value');

        $endTimeInMils = $get_end_time ? Carbon::parse($get_end_time)->timestamp : 0;

        $isTime = $curTimeInMils >= $endTimeInMils;

        return response()->json([
            'started' => $started === '1',
            'current_time' => $current_time->format('H:i:s'),
            'curTimeInMils' => $curTimeInMils,
            'expected_time' => $get_end_time,
            'endTimeInMils' => $endTimeInMils,
            'isTime' => $isTime,
        ]);
    }
}
