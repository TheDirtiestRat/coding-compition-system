<?php

namespace Tests\Feature;

use App\Models\Candidates;
use App\Models\Challenges;
use App\Models\Events;
use App\Models\User;
use App\Support\CompetitionTimer;
use Tests\TestCase;

class ActiveEventAdmissionTest extends TestCase
{
    public function test_old_event_candidate_cannot_work_while_another_event_is_running(): void
    {
        [$candidate, $challenge] = $this->contestant(false);
        Events::create(['title' => 'Current event', 'isActive' => true]);
        $this->startCompetition();

        $this->actingAs($candidate->user)->get('/code-editor/'.$challenge->id)
            ->assertRedirect('/code-problems');
        $this->get('/code-editor')->assertRedirect('/code-problems');
        $this->postJson('/api/submit-code-for-review', ['challenge_id' => $challenge->id, 'code' => 'old code'])
            ->assertForbidden();
        $this->get('/code-problems')->assertOk()
            ->assertViewHas('easy_challenges', fn ($challenges) => $challenges->isEmpty())
            ->assertViewHas('unsolved_challenge_id', null)
            ->assertViewHas('competition_started', false);
        $this->getJson('/api/get-challenge-details/'.$challenge->id)->assertNotFound();
        $this->assertDatabaseCount('submissions', 0);
    }

    public function test_active_candidate_can_open_details_editor_and_submit(): void
    {
        [$candidate, $challenge] = $this->contestant(true);
        $this->startCompetition();

        $this->actingAs($candidate->user)->get('/code-problems')->assertOk()
            ->assertViewHas('easy_challenges', fn ($challenges) => $challenges->contains('id', $challenge->id))
            ->assertViewHas('competition_started', true);
        $this->getJson('/api/get-challenge-details/'.$challenge->id)->assertOk();
        $this->get('/code-editor/'.$challenge->id)->assertOk();
        $this->postJson('/api/submit-code-for-review', ['challenge_id' => $challenge->id, 'code' => 'current code'])
            ->assertOk();
        $this->assertDatabaseHas('submissions', ['candidate_id' => $candidate->id, 'code' => 'current code']);
    }

    public function test_event_switch_blocks_an_already_open_editors_submission(): void
    {
        [$candidate, $challenge] = $this->contestant(true);
        $this->startCompetition();
        $this->actingAs($candidate->user)->get('/code-editor/'.$challenge->id)->assertOk();

        Events::whereKey($candidate->event_id)->update(['isActive' => false]);
        Events::create(['title' => 'Next event', 'isActive' => true]);

        $this->postJson('/api/submit-code-for-review', ['challenge_id' => $challenge->id, 'code' => 'stale code'])
            ->assertForbidden();
        $this->assertDatabaseCount('submissions', 0);
    }

    public function test_no_active_event_rejects_candidate_work(): void
    {
        [$candidate, $challenge] = $this->contestant(false);
        $this->startCompetition();

        $this->actingAs($candidate->user)->get('/code-editor/'.$challenge->id)
            ->assertRedirect('/code-problems');
        $this->postJson('/api/submit-code-for-review', ['challenge_id' => $challenge->id, 'code' => 'code'])
            ->assertForbidden();
    }

    public function test_admin_without_candidate_uses_admin_tools_and_candidate_routes_fail_cleanly(): void
    {
        [, $challenge] = $this->contestant(true);
        $this->startCompetition();
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));

        $this->get('/code-problems')->assertOk()->assertViewHas('competition_started', false);
        $this->get('/code-editor/'.$challenge->id)->assertRedirect('/code-problems');
        $this->getJson('/api/get-challenge-details/'.$challenge->id)->assertNotFound();
        $this->postJson('/api/submit-code-for-review', ['challenge_id' => $challenge->id, 'code' => 'code'])
            ->assertNotFound();
        $this->getJson('/admin/api/get-challenge-details/'.$challenge->id)->assertOk();
    }

    private function contestant(bool $active): array
    {
        $event = Events::create(['title' => 'Event', 'isActive' => $active]);
        $candidate = Candidates::create([
            'event_id' => $event->id,
            'fullname' => 'Contestant',
            'user_id' => User::factory()->create()->id,
        ]);
        $challenge = Challenges::create([
            'event_id' => $event->id,
            'title' => 'Hello',
            'description' => 'Print hello',
            'difficulty' => 'easy',
            'language' => 'python',
            'points' => 100,
            'code_solution' => 'print("hello")',
        ]);

        return [$candidate, $challenge];
    }

    private function startCompetition(): void
    {
        CompetitionTimer::save([
            'start_compition' => '1',
            'expected_end_time' => now()->addHour()->toDateTimeString(),
        ]);
    }
}
