<?php

namespace Tests\Feature;

use App\Events\CountdownEvent;
use App\Http\Controllers\AdminCountdownController;
use App\Models\User;
use App\Support\CompetitionTimer;
use Carbon\Carbon;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TimerTransitionLockTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_reset_at_the_first_pause_read_cannot_restore_remaining_time(): void
    {
        Event::fake([CountdownEvent::class]);
        Carbon::setTestNow('2026-11-13 10:00:00');
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));
        $this->postJson('/admin/countdown/start', ['duration_ms' => 60000])->assertOk();
        Carbon::setTestNow('2026-11-13 10:00:20');
        $interleaved = false;
        $baseline = DB::transactionLevel();

        DB::listen(function (QueryExecuted $query) use (&$interleaved, $baseline) {
            if ($interleaved || ! str_starts_with(strtolower($query->sql), 'select')
                || ! str_contains($query->sql, 'options')) {
                return;
            }

            $interleaved = true;
            $this->assertGreaterThan($baseline, DB::transactionLevel());
            // Reset wins before pause reads timer state, on the same locked path.
            app(AdminCountdownController::class)->reset();
        });

        $this->postJson('/admin/countdown/pause')->assertJson(['status' => 'not_running']);
        $this->assertTrue($interleaved);
        $this->assertFalse(CompetitionTimer::state()['paused']);
        $this->assertNull(CompetitionTimer::state()['remainingMs']);
        Event::assertNotDispatched(CountdownEvent::class, fn ($event) => $event->type === 'paused');
    }

    public function test_every_state_read_for_start_and_pause_holds_the_shared_lock(): void
    {
        Event::fake([CountdownEvent::class]);
        Carbon::setTestNow('2026-11-13 10:00:00');
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));
        $baseline = DB::transactionLevel();
        $this->assertDatabaseCount('options', 0);
        $reads = 0;
        $locked = false;

        DB::listen(function (QueryExecuted $query) use ($baseline, &$reads, &$locked) {
            if (! str_starts_with(strtolower($query->sql), 'select')
                || ! str_contains($query->sql, 'options')) {
                return;
            }
            if (str_contains($query->sql, 'select *')) {
                $locked = true;
            } elseif (str_contains($query->sql, 'option_value') && str_contains($query->sql, 'option_name')) {
                $this->assertTrue($locked);
                $this->assertGreaterThan($baseline, DB::transactionLevel());
                $reads++;
            }
        });

        // No seeder: the first request must establish the same lock row.
        $this->postJson('/admin/countdown/start', ['duration_ms' => 60000])->assertOk();
        $locked = false;
        $this->postJson('/admin/countdown/pause')->assertOk();
        $locked = false;
        $this->postJson('/admin/countdown/start')->assertOk();
        $this->assertSame(3, $reads);
        $this->postJson('/admin/countdown/reset')->assertOk();
    }

    public function test_seeded_lock_row_is_preserved_and_events_run_after_transition_commits(): void
    {
        DB::table('options')->insert([
            'id' => 1,
            'option_name' => 'registration_open',
            'option_value' => '1',
            'option_datatype' => 'boolean',
        ]);
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));
        Carbon::setTestNow('2026-11-13 10:00:00');
        $baseline = DB::transactionLevel();
        $events = 0;
        Event::listen(CountdownEvent::class, function () use ($baseline, &$events) {
            $this->assertSame($baseline, DB::transactionLevel());
            $events++;
        });

        $this->postJson('/admin/countdown/start', ['duration_ms' => 60000])->assertOk();
        $this->postJson('/admin/countdown/pause')->assertOk();
        $this->postJson('/admin/countdown/start')->assertOk();
        $this->postJson('/admin/timer/set_timer', ['setHours' => 0, 'setMinutes' => 1, 'setSeconds' => 0])
            ->assertOk();
        $this->postJson('/admin/countdown/reset')->assertOk();

        $this->assertSame(5, $events);
        $this->assertDatabaseHas('options', ['id' => 1, 'option_name' => 'registration_open', 'option_value' => '1']);
    }
}
