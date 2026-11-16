<?php

namespace Tests\Feature;

use App\Support\CompetitionTimer;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HomepageStatusTest extends TestCase
{
    #[DataProvider('timerStates')]
    public function test_homepage_displays_the_existing_timer_state(array $options, string $status): void
    {
        CompetitionTimer::save($options);

        $this->get('/')->assertOk()->assertSee('Competition:')->assertSee($status);
    }

    public static function timerStates(): array
    {
        return [
            'not started' => [[], 'Not started'],
            'running' => [['start_compition' => '1', 'expected_end_time' => '2099-01-01 12:00:00'], 'In progress'],
            'paused' => [['start_compition' => '0', 'competition_remaining_ms' => '30000'], 'Paused'],
            'expired' => [['start_compition' => '1', 'expected_end_time' => '2000-01-01 12:00:00'], 'Ended'],
            'missing deadline' => [['start_compition' => '1'], 'Not started'],
        ];
    }
}
