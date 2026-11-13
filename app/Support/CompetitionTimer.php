<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CompetitionTimer
{
    public static function state(): array
    {
        $options = DB::table('options')->pluck('option_value', 'option_name');
        $now = Carbon::now();
        $deadline = $options['expected_end_time'] ?? null;
        $endTime = $deadline && $deadline !== '00:00:00' ? Carbon::parse($deadline) : null;
        $started = ($options['start_compition'] ?? '0') === '1';
        $remaining = $options['competition_remaining_ms'] ?? '';

        return [
            'started' => $started,
            'paused' => ! $started && $remaining !== '',
            'current_time' => $now->format('H:i:s'),
            'curTimeInMils' => $now->timestamp,
            'serverTime' => $now->getTimestampMs(),
            'expected_time' => $deadline,
            'endTimeInMils' => $endTime?->timestamp,
            'endTime' => $started ? $endTime?->getTimestampMs() : null,
            'remainingMs' => $remaining !== '' ? (int) $remaining : null,
            'isTime' => $started && $endTime && $now->greaterThanOrEqualTo($endTime),
        ];
    }

    public static function transition(callable $callback): mixed
    {
        return DB::transaction(function () use ($callback) {
            // A primary key also serializes first requests before options are seeded.
            DB::table('options')->insertOrIgnore([
                'id' => 1,
                'option_name' => 'competition_timer_lock',
                'option_value' => '0',
                'option_datatype' => 'boolean',
            ]);
            DB::table('options')->where('id', 1)->lockForUpdate()->first();

            return $callback();
        }, 5);
    }

    public static function save(array $values): void
    {
        self::transition(function () use ($values) {
            foreach ($values as $name => $value) {
                DB::table('options')->updateOrInsert(
                    ['option_name' => $name],
                    ['option_value' => (string) $value, 'option_datatype' => 'text']
                );
            }
        });
    }
}
