<?php

namespace App\Http\Controllers;

use App\Events\CountdownEvent;
use App\Support\CompetitionTimer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminCountdownController extends Controller
{
    public function start(Request $request)
    {
        $result = CompetitionTimer::transition(function () use ($request) {
            $timer = CompetitionTimer::state();
            if ($timer['paused']) {
                $durationMs = $timer['remainingMs'];
            } else {
                $validated = $request->validate(['duration_ms' => 'required|integer|min:1']);
                $durationMs = (int) $validated['duration_ms'];
            }

            if ($durationMs <= 0) {
                return null;
            }

            return $this->start_timer($durationMs, $timer['paused'] ? 'resume' : 'start');
        });

        return $this->started_response($result);
    }

    public function pause()
    {
        $remaining = CompetitionTimer::transition(function () {
            $timer = CompetitionTimer::state();
            if (! $timer['started'] || ! $timer['endTime'] || $timer['isTime']) {
                return null;
            }

            $remaining = max(0, $timer['endTime'] - $timer['serverTime']);
            CompetitionTimer::save([
                'start_compition' => '0',
                'competition_remaining_ms' => $remaining,
            ]);

            return $remaining;
        });

        if ($remaining === null) {
            return response()->json(['status' => 'not_running']);
        }

        broadcast(new CountdownEvent('paused', ['remainingMs' => $remaining]));

        return response()->json(['remainingMs' => $remaining]);
    }

    public function reset()
    {
        CompetitionTimer::save([
            'start_compition' => '0',
            'expected_end_time' => '00:00:00',
            'competition_remaining_ms' => '',
        ]);
        broadcast(new CountdownEvent('reset'));

        return response()->json(['status' => 'reset']);
    }

    public function set_timer(Request $request)
    {
        $validated = $request->validate([
            'setHours' => 'required|integer|min:0',
            'setMinutes' => 'required|integer|min:0|max:59',
            'setSeconds' => 'required|integer|min:0|max:59',
        ]);
        $durationMs = ((int) $validated['setHours'] * 3600
            + (int) $validated['setMinutes'] * 60
            + (int) $validated['setSeconds']) * 1000;

        if ($durationMs <= 0) {
            return response()->json(['error' => 'Invalid duration'], 422);
        }

        $result = CompetitionTimer::transition(fn () => $this->start_timer($durationMs));

        return $this->started_response($result);
    }

    public function get_timer()
    {
        return response()->json(CompetitionTimer::state());
    }

    private function start_timer(int $durationMs, string $type = 'start')
    {
        $now = Carbon::now();
        $endTime = $now->copy()->addMilliseconds($durationMs);
        CompetitionTimer::save([
            'set_time' => $now->toDateTimeString(),
            'expected_end_time' => $endTime->format('Y-m-d H:i:s.u'),
            'start_compition' => '1',
            'competition_remaining_ms' => '',
        ]);

        return ['type' => $type, 'data' => [
            'endTime' => $endTime->getTimestampMs(),
            'current_time' => $now->format('H:i:s'),
            'formatted_set_time' => $now->format('H:i:s'),
            'formatted_end_time' => $endTime->format('H:i:s'),
        ]];
    }

    private function started_response(?array $result)
    {
        if ($result === null) {
            return response()->json(['error' => 'Invalid duration'], 422);
        }

        broadcast(new CountdownEvent($result['type'], ['endTime' => $result['data']['endTime']]));

        return response()->json($result['data']);
    }
}
