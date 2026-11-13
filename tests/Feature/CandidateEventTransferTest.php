<?php

namespace Tests\Feature;

use App\Http\Middleware\IsTimerStop;
use App\Models\Candidates;
use App\Models\Challenges;
use App\Models\Events;
use App\Models\ProgrammingLanguage;
use App\Models\Submissions;
use App\Models\User;
use App\Services\CodeRunner;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CandidateEventTransferTest extends TestCase
{
    public function test_candidates_with_pending_or_reviewed_submissions_cannot_change_events(): void
    {
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));
        $destination = Events::create(['title' => 'Destination']);

        foreach ([false, true] as $reviewed) {
            $candidate = $this->candidate();
            $submission = Submissions::create([
                'candidate_id' => $candidate->id,
                'challenge_id' => $this->challenge($candidate->event_id)->id,
                'code' => 'original',
                'score' => $reviewed ? 50 : 0,
                'is_score_submitted' => $reviewed,
            ]);
            $originalUser = $candidate->user->getAttributes();

            $this->postJson('/admin/update-candidate', $this->edits($candidate, $destination->id))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('event_id');

            $this->assertEquals($candidate->event_id, $candidate->fresh()->event_id);
            $this->assertSame($candidate->fullname, $candidate->fresh()->fullname);
            $this->assertSame($originalUser, $candidate->user->fresh()->getAttributes());
            $this->assertSame('original', $submission->fresh()->code);
        }
    }

    public function test_candidate_without_submissions_can_change_events(): void
    {
        $candidate = $this->candidate();
        $destination = Events::create(['title' => 'Destination']);

        $this->actingAs(User::factory()->create(['user_type' => 'admin']))
            ->post('/admin/update-candidate', $this->edits($candidate, $destination->id))
            ->assertSessionHas('success');

        $this->assertEquals($destination->id, $candidate->fresh()->event_id);
    }

    public function test_candidate_with_submissions_can_edit_identity_in_the_same_event(): void
    {
        $candidate = $this->candidate();
        Submissions::create([
            'candidate_id' => $candidate->id,
            'challenge_id' => $this->challenge($candidate->event_id)->id,
            'code' => 'original',
        ]);

        $this->actingAs(User::factory()->create(['user_type' => 'admin']))
            ->post('/admin/update-candidate', $this->edits($candidate, $candidate->event_id))
            ->assertSessionHas('success');

        $this->assertSame('Updated Candidate', $candidate->fresh()->fullname);
        $this->assertSame('updated'.$candidate->id, $candidate->user->fresh()->name);
        $this->assertTrue(Hash::check('new-password', $candidate->user->fresh()->password));
        $this->assertDatabaseCount('submissions', 1);
    }

    public function test_leaderboards_exclude_existing_scores_from_other_events(): void
    {
        $candidate = $this->candidate();
        $otherEvent = Events::create(['title' => 'Other']);
        foreach ([$candidate->event_id => 20, $otherEvent->id => 90] as $eventId => $score) {
            Submissions::create([
                'candidate_id' => $candidate->id,
                'challenge_id' => $this->challenge($eventId)->id,
                'code' => 'original',
                'score' => $score,
                'is_score_submitted' => true,
            ]);
        }

        $this->actingAs(User::factory()->create(['user_type' => 'admin']))
            ->get('/admin/dashboard')->assertOk()
            ->assertViewHas('candidates', fn ($candidates) => (int) $candidates->first()->total_score === 20);
        $this->get('/admin/results')->assertOk()
            ->assertViewHas('candidates', fn ($candidates) => (int) $candidates->first()->total_score === 20);
        $this->assertDatabaseCount('submissions', 2);
    }

    public function test_candidate_moved_during_code_execution_cannot_save_old_event_submission(): void
    {
        $candidate = $this->candidate();
        $challenge = $this->challenge($candidate->event_id);
        $language = ProgrammingLanguage::create([
            'name' => 'python', 'run_command' => 'python {file}', 'file_extension' => '.py', 'is_active' => true,
        ]);
        $challenge->update(['language_id' => $language->id]);
        $challenge->testCases()->create(['expected_output' => 'hello', 'points' => 50]);
        $destination = Events::create(['title' => 'Destination']);
        $this->mock(CodeRunner::class)->shouldReceive('run')->once()->andReturnUsing(function () use ($candidate, $destination) {
            $candidate->update(['event_id' => $destination->id]);

            return 'hello';
        });

        $this->withoutMiddleware(IsTimerStop::class)->actingAs($candidate->user)
            ->postJson('/api/submit-code-for-review', ['challenge_id' => $challenge->id, 'code' => 'print("hello")'])
            ->assertConflict();

        $this->assertDatabaseCount('submissions', 0);
    }

    private function edits(Candidates $candidate, int $eventId): array
    {
        return [
            'candidate_id' => $candidate->id,
            'event_id' => $eventId,
            'edit_name' => 'Updated Candidate',
            'edit_username' => 'updated'.$candidate->id,
            'edit_user_password' => 'new-password',
        ];
    }

    private function candidate(): Candidates
    {
        return Candidates::create([
            'event_id' => Events::create(['title' => 'Competition', 'isActive' => true])->id,
            'fullname' => 'Contestant',
            'user_id' => User::factory()->create()->id,
        ]);
    }

    private function challenge(int $eventId): Challenges
    {
        return Challenges::create([
            'event_id' => $eventId,
            'title' => 'Hello',
            'description' => 'Print hello',
            'difficulty' => 'easy',
            'language' => 'python',
            'points' => 100,
            'code_solution' => 'print("hello")',
        ]);
    }
}
