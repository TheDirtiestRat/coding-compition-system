<?php

namespace App\Http\Controllers;

use App\Events\CountdownEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DateTime;
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

    public function set_timer(Request $request)
    {
        $validatedData = $request->validate([
            'setHours' => 'required',
            'setMinutes' => 'required',
            'setSeconds' => 'required',
        ]);

        // set the timer and its expected end of time
        $current_time = date("h:i:s");

        $set_time = $validatedData['setHours'] . ":" . $validatedData['setMinutes'] . ":" . $validatedData['setSeconds'];
        $timestamp = strtotime($set_time);
        $formatted_set_time = date("h:i:s", $timestamp);

        // $set_time_miliseconds = ($validatedData['setHours'] * 3600000) + ($validatedData['setMinutes'] * 60000) + ($validatedData['setSeconds'] * 1000);
        // $end_time_milliseconds = Carbon::now()->addMilliseconds($set_time_miliseconds);
        // $formatted_end_time = $end_time_milliseconds->format("H:i:s");

        // trying new
        $set_date = new DateTime($current_time);
        $set_date->modify('+'.$validatedData['setHours'] .' hours +'.$validatedData['setMinutes'].' minutes +'.$validatedData['setSeconds'].' seconds');

        $formatted_end_time = $set_date->format("h:i:s");

        $date_set = date_create($formatted_set_time);
        $date_end = date_create($formatted_end_time);
        $interval = date_diff($date_set, $date_end);

        DB::table('options')
            ->where('option_name', "set_time")
            ->update(['option_value' => $formatted_set_time]);
        DB::table('options')
            ->where('option_name', "expected_end_time")
            ->update(['option_value' => $formatted_end_time]);

        return response()->json([
            'current_time' => $current_time,
            'formatted_set_time' => $formatted_set_time,
            'formatted_end_time' => $formatted_end_time,
            "interval" => $interval,
        ]);
    }

    public function get_timer()
    {
        // get the expected end of time and compare it to the current time
        $isTime = false;

        $current_time = date("h:i:s");
        $curTimeInMils = strtotime($current_time);

        $get_end_time = DB::table('options')
            ->where('option_name', "expected_end_time")->first('option_value');
        $end_time = date($get_end_time->option_value);
        $endTimeInMils = strtotime($end_time);

        if($curTimeInMils >= $endTimeInMils) {
            $isTime = true;
        }else {
            $isTime = false;
        }
        
        return response()->json([
            'current_time' => $current_time,
            'curTimeInMils' => $curTimeInMils,
            'expected_time' => $get_end_time->option_value,
            'endTimeInMils' => $endTimeInMils,
            'isTime' => $isTime,
        ]);
    }
}
