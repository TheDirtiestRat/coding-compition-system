<?php

namespace App\Http\Middleware;

use App\Support\CompetitionTimer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsTimerStop
{
    public function handle(Request $request, Closure $next): Response
    {
        $timer = CompetitionTimer::state();
        if (! $timer['started']) {
            return $request->expectsJson()
                ? response()->json(['message' => 'The competition is not running.'], 409)
                : redirect('/code-problems');
        }

        if ($timer['isTime']) {
            return $request->expectsJson()
                ? response()->json(['message' => 'The competition has ended.'], 409)
                : redirect('/competition-ended');
        }

        return $next($request);
    }
}
