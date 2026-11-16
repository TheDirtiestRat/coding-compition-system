<?php

namespace Tests\Feature;

use App\Models\Candidates;
use App\Models\Challenges;
use App\Models\Events;
use App\Models\Submissions;
use App\Models\User;
use Tests\TestCase;

class CertificatesTest extends TestCase
{
    public function test_certificates_use_active_event_rankings_and_preserve_selected_winner_rank(): void
    {
        $event = Events::create(['title' => 'Competition', 'isActive' => true]);
        $challenge = Challenges::create([
            'event_id' => $event->id, 'title' => 'Problem', 'description' => 'Solve it',
            'points' => 100, 'difficulty' => 'easy', 'language' => 'python', 'code_solution' => 'reference',
        ]);
        $people = collect([90, 70, 30, 0])->map(function ($score, $index) use ($event, $challenge) {
            $candidate = Candidates::create([
                'event_id' => $event->id, 'fullname' => 'Competitor '.$index,
                'user_id' => User::factory()->create()->id,
            ]);
            Submissions::create([
                'candidate_id' => $candidate->id, 'challenge_id' => $challenge->id,
                'code' => 'solution', 'score' => $score, 'is_score_submitted' => true,
            ]);

            return $candidate;
        });
        $outsider = Candidates::create([
            'event_id' => Events::create(['title' => 'Other event'])->id,
            'fullname' => 'Other event competitor', 'user_id' => User::factory()->create()->id,
        ]);
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));
        $this->get('/admin/certificates')->assertOk()
            ->assertViewHas('certificates', fn ($items) => $items->count() === 4)
            ->assertDontSee($outsider->fullname);
        $this->get('/admin/certificates?type=winner')->assertOk()
            ->assertViewHas('certificates', fn ($items) => $items->count() === 3);
        $this->get('/admin/certificates?type=winner&candidate_id='.$people[1]->id)->assertOk()
            ->assertViewHas('certificates', fn ($items) => $items->count() === 1 && $items->first()['rank'] === 2);
        $this->getJson('/admin/certificates?candidate_id='.$outsider->id)
            ->assertUnprocessable()->assertJsonValidationErrors('candidate_id');
    }

    public function test_certificates_require_an_organizer_and_handle_no_active_event(): void
    {
        $this->get('/admin/certificates')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/admin/certificates')->assertForbidden();
        $this->actingAs(User::factory()->create(['user_type' => 'admin']))
            ->get('/admin/certificates')->assertOk()->assertSee('No eligible competitors for this selection.');
    }
}
