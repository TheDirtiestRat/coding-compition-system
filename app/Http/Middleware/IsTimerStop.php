<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\DB;

class IsTimerStop
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // come from AI coz I my brain cells cant
        // 1. Use ->value() to get the specific column value directly
        // 2. I'm assuming your value column is called 'option_value'
        $status = DB::table('options')
            ->where('option_name', 'start_compition')
            ->value('option_value');
        
        // check if the current time overlaps to the expected end time if it is then it is time.
        $current_time = date("h:i:s");
        $curTimeInMils = strtotime($current_time);

        $get_end_time = DB::table('options')
            ->where('option_name', "expected_end_time")->first('option_value');
        $end_time = date($get_end_time->option_value);
        $endTimeInMils = strtotime($end_time);

        // 3. Check if the status is specifically '0' (Competition Stopped)
        // if ($status === '0')
        if ($curTimeInMils >= $endTimeInMils) {
            abort(403, 'The competition has ended. You cannot access this page anymore.');
        }

        // if the compition has ended then we cannot access this pages
        // $option = DB::table('options')
        //     ->select('option_name')
        //     ->where('option_name', "start_compition")
        //     ->get();
        
        // if ($option[0] == '0') {
        //     abort(403, 'You cannot Access this Anymore.');
        // }

        return $next($request);
    }
}
