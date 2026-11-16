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
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ChallengeRubricLockTest extends TestCase
{
    public function test_submitted_challenges_keep_the_original_task_rubric_and_grade(): void
    {
        foreach ([false, true] as $reviewed) {
            [$candidate, $challenge] = $this->fixture();
            $submission = Submissions::create([
                'candidate_id' => $candidate->id,
                'challenge_id' => $challenge->id,
                'code' => 'original',
                'score' => $reviewed ? 100 : 0,
                'is_score_submitted' => $reviewed,
            ]);
            $originalTask = $challenge->fresh()->getAttributes();
            $originalCases = $challenge->testCases()->get()->toArray();
            $originalSubmission = $submission->fresh()->getAttributes();
            $replacementLanguage = ProgrammingLanguage::create([
                'name' => 'replacement-'.$challenge->id,
                'file_extension' => '.py',
                'run_command' => 'python {file}',
                'is_active' => true,
            ]);

            $this->actingAs(User::factory()->create(['user_type' => 'admin']))
                ->postJson('/admin/save-challenge', [
                    'challenge_id' => $challenge->id,
                    'event_id' => Events::create(['title' => 'Replacement event'])->id,
                    'title' => 'Replacement task',
                    'description' => 'Different task',
                    'difficulty' => 'hard',
                    'language' => $replacementLanguage->name,
                    'language_id' => $replacementLanguage->id,
                    'points' => 10,
                    'code_solution' => 'replacement',
                    'output_mode' => 'numeric',
                    'output_tolerance' => 0.5,
                    'test_cases' => [['input' => '', 'expected_output' => 'replacement', 'points' => 10]],
                ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('challenge_id');

            $this->assertSame($originalTask, $challenge->fresh()->getAttributes());
            $this->assertSame($originalCases, $challenge->testCases()->get()->toArray());
            $this->assertSame($originalSubmission, $submission->fresh()->getAttributes());
        }
    }

    #[DataProvider('rubricChanges')]
    public function test_rubric_changed_during_execution_requires_a_new_submission(bool $changeSettings): void
    {
        [$candidate, $challenge] = $this->fixture();
        $this->mock(CodeRunner::class)->shouldReceive('run')->once()->andReturnUsing(function () use ($challenge, $changeSettings) {
            if ($changeSettings) {
                $challenge->update(['output_mode' => 'numeric', 'output_tolerance' => 0.5]);
            } else {
                $challenge->testCases()->delete();
                $challenge->testCases()->create(['expected_output' => 'different', 'points' => 10]);
            }

            return 'hello';
        });

        $this->withoutMiddleware(IsTimerStop::class)->actingAs($candidate->user)
            ->postJson('/api/submit-code-for-review', ['challenge_id' => $challenge->id, 'code' => 'original'])
            ->assertConflict();

        $this->assertDatabaseCount('submissions', 0);
    }

    public static function rubricChanges(): array
    {
        return ['cases' => [false], 'output settings' => [true]];
    }

    private function fixture(): array
    {
        $event = Events::create(['title' => 'Competition', 'isActive' => true]);
        $candidate = Candidates::create([
            'event_id' => $event->id,
            'fullname' => 'Contestant',
            'user_id' => User::factory()->create()->id,
        ]);
        $language = ProgrammingLanguage::create([
            'name' => 'python-'.$event->id,
            'file_extension' => '.py',
            'run_command' => 'python {file}',
            'is_active' => true,
        ]);
        $challenge = Challenges::create([
            'event_id' => $event->id,
            'title' => 'Hello',
            'description' => 'Print hello',
            'difficulty' => 'easy',
            'language' => $language->name,
            'language_id' => $language->id,
            'points' => 100,
            'code_solution' => 'original',
        ]);
        $challenge->testCases()->create(['expected_output' => 'hello', 'points' => 100]);

        return [$candidate, $challenge];
    }
}
