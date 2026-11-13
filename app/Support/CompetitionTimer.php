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

    public static function save(array $values): void
    {
        DB::transaction(function () use ($values) {
            foreach ($values as $name => $value) {
                DB::table('options')->updateOrInsert(
                    ['option_name' => $name],
                    ['option_value' => (string) $value, 'option_datatype' => 'text']
                );
            }
        });
    }
}
