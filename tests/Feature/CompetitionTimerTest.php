<?php

namespace Tests\Feature;

use App\Events\CountdownEvent;
use App\Models\User;
use App\Support\CompetitionTimer;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CompetitionTimerTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_deadline_keeps_its_date_across_midnight(): void
    {
        Event::fake([CountdownEvent::class]);
        Carbon::setTestNow(Carbon::parse('2026-11-13 23:59:00'));
        $this->actingAs(User::factory()->create(['user_type' => 'admin']))
            ->postJson('/admin/timer/set_timer', ['setHours' => 0, 'setMinutes' => 2, 'setSeconds' => 0])
            ->assertOk();

        Carbon::setTestNow(Carbon::parse('2026-11-14 00:00:00'));
        $this->getJson('/countdown')->assertJson(['started' => true, 'isTime' => false]);
        Carbon::setTestNow(Carbon::parse('2026-11-14 00:01:00'));
        $this->getJson('/countdown')->assertJson(['isTime' => true]);
    }

    public function test_pause_resume_and_reset_share_the_same_state(): void
    {
        Event::fake([CountdownEvent::class]);
        Carbon::setTestNow(Carbon::parse('2026-11-13 10:00:00'));
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));
        $this->postJson('/admin/countdown/start', ['duration_ms' => 60000])->assertOk();
        Carbon::setTestNow(Carbon::parse('2026-11-13 10:00:20'));
        $this->postJson('/admin/countdown/pause')->assertJson(['remainingMs' => 40000]);
        $this->getJson('/countdown')->assertJson(['started' => false, 'paused' => true, 'isTime' => false]);
        $this->postJson('/api/submit-code-for-review', [])->assertStatus(409);

        Carbon::setTestNow(Carbon::parse('2026-11-13 11:00:00'));
        $this->postJson('/admin/countdown/start')->assertOk();
        $this->assertSame(40000, CompetitionTimer::state()['endTime'] - CompetitionTimer::state()['serverTime']);
        Event::assertDispatched(CountdownEvent::class, fn ($event) => $event->type === 'resume');
        $this->postJson('/admin/countdown/reset')->assertOk();
        $this->getJson('/countdown')->assertJson(['started' => false, 'paused' => false, 'isTime' => false]);
    }
}
