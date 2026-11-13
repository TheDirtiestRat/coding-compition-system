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
use Tests\TestCase;

class SubmissionRulesTest extends TestCase
{
    public function test_challenge_details_keep_photos_and_reject_other_events(): void
    {
        $candidate = $this->candidate();
        $challenge = $this->challenge($candidate->event_id);
        $challenge->photos()->create(['photo_path' => 'challenge-photos/example.png']);
        $otherChallenge = $this->challenge(Events::create(['title' => 'Other'])->id);

        $this->actingAs($candidate->user)
            ->getJson('/api/get-challenge-details/'.$challenge->id)
            ->assertOk()
            ->assertJsonCount(1, 'photos_list');

        $this->getJson('/api/get-challenge-details/'.$otherChallenge->id)->assertNotFound();
    }

    public function test_missing_language_leaves_test_cases_awaiting_review(): void
    {
        $candidate = $this->candidate();
        $challenge = $this->challenge($candidate->event_id);
        $challenge->testCases()->create(['expected_output' => 'hello', 'points' => 100]);

        $this->withoutMiddleware(IsTimerStop::class)->actingAs($candidate->user)
            ->postJson('/api/submit-code-for-review', ['challenge_id' => $challenge->id, 'code' => 'print("hello")'])
            ->assertOk()
            ->assertJsonPath('auto_reviewed', false);

        $this->assertDatabaseHas('submissions', [
            'candidate_id' => $candidate->id,
            'challenge_id' => $challenge->id,
            'is_score_submitted' => false,
            'score' => 0,
        ]);
    }

    public function test_submission_rejects_a_challenge_from_another_event(): void
    {
        $candidate = $this->candidate();
        $challenge = $this->challenge(Events::create(['title' => 'Other'])->id);

        $this->withoutMiddleware(IsTimerStop::class)->actingAs($candidate->user)
            ->postJson('/api/submit-code-for-review', ['challenge_id' => $challenge->id, 'code' => 'print("hello")'])
            ->assertNotFound();

        $this->assertDatabaseCount('submissions', 0);
    }

    public function test_inactive_language_does_not_execute_test_cases(): void
    {
        $candidate = $this->candidate();
        $challenge = $this->challenge($candidate->event_id);
        $language = ProgrammingLanguage::create([
            'name' => 'python',
            'file_extension' => '.py',
            'run_command' => 'python {file}',
            'is_active' => false,
        ]);
        $challenge->update(['language_id' => $language->id]);
        $challenge->testCases()->create(['expected_output' => 'hello', 'points' => 100]);
        $this->mock(CodeRunner::class)->shouldNotReceive('run');

        $this->withoutMiddleware(IsTimerStop::class)->actingAs($candidate->user)
            ->postJson('/api/submit-code-for-review', ['challenge_id' => $challenge->id, 'code' => 'print("hello")'])
            ->assertOk()
            ->assertJsonPath('auto_reviewed', false);
    }

    public function test_reviewed_submission_cannot_be_resubmitted_or_scored_again(): void
    {
        $candidate = $this->candidate();
        $challenge = $this->challenge($candidate->event_id);
        $submission = Submissions::create([
            'candidate_id' => $candidate->id,
            'challenge_id' => $challenge->id,
            'code' => 'original',
            'score' => 75,
            'is_score_submitted' => true,
        ]);

        $this->withoutMiddleware(IsTimerStop::class)->actingAs($candidate->user)
            ->postJson('/api/submit-code-for-review', ['challenge_id' => $challenge->id, 'code' => 'replacement'])
            ->assertConflict();

        $this->actingAs(User::factory()->create(['user_type' => 'admin']))
            ->postJson('/admin/api/submissions/'.$submission->id.'/submit-score', ['score' => 100])
            ->assertConflict();

        $this->assertSame('original', $submission->fresh()->code);
        $this->assertEquals(75, $submission->fresh()->score);
    }

    private function candidate(): Candidates
    {
        return Candidates::create([
            'event_id' => Events::create(['title' => 'Competition', 'isActive' => true])->id,
            'fullname' => 'Contestant',
            'user_id' => User::factory()->create()->id,
        ]);
    }

    private function challenge(int $event_id): Challenges
    {
        return Challenges::create([
            'event_id' => $event_id,
            'title' => 'Hello',
            'description' => 'Print hello',
            'difficulty' => 'easy',
            'language' => 'python',
            'points' => 100,
            'code_solution' => 'print("hello")',
        ]);
    }
}
