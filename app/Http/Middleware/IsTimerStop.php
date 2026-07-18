<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IsTimerStop
{
    public function handle(Request $request, Closure $next): Response
    {
        $started = DB::table('options')
            ->where('option_name', 'start_compition')
            ->value('option_value');

        if ($started !== '1') {
            return redirect('/code-problems');
        }

        $current_time = Carbon::now();
        $curTimeInMils = $current_time->timestamp;

        $get_end_time = DB::table('options')
            ->where('option_name', "expected_end_time")->value('option_value');

        if ($get_end_time && $get_end_time !== '00:00:00') {
            $endTimeInMils = Carbon::parse($get_end_time)->timestamp;

            if ($curTimeInMils >= $endTimeInMils) {
                return redirect('/competition-ended');
            }
        }

        return $next($request);
    }
}
