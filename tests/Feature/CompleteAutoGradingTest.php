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

class CompleteAutoGradingTest extends TestCase
{
    #[DataProvider('gradingCases')]
    public function test_only_full_passing_coverage_finalizes_review(array $points, int $maximum, bool $activeLanguage, bool $passes, bool $autoReviewed): void
    {
        $candidate = Candidates::create([
            'event_id' => Events::create(['title' => 'Competition', 'isActive' => true])->id,
            'fullname' => 'Contestant',
            'user_id' => User::factory()->create()->id,
        ]);
        $language = ProgrammingLanguage::create([
            'name' => 'python',
            'file_extension' => '.py',
            'run_command' => 'python {file}',
            'is_active' => $activeLanguage,
        ]);
        $challenge = Challenges::create([
            'event_id' => $candidate->event_id,
            'title' => 'Coverage',
            'description' => 'Print hello',
            'difficulty' => 'easy',
            'language' => 'python',
            'language_id' => $language->id,
            'points' => $maximum,
            'code_solution' => 'reference',
        ]);
        foreach ($points as $weight) {
            $challenge->testCases()->create(['expected_output' => 'hello', 'points' => $weight]);
        }
        $runs = $activeLanguage ? count($points) : 0;
        $this->mock(CodeRunner::class)->shouldReceive('run')->times($runs)
            ->andReturn($passes ? 'hello' : 'wrong');

        $this->withoutMiddleware(IsTimerStop::class)->actingAs($candidate->user)
            ->postJson('/api/submit-code-for-review', ['challenge_id' => $challenge->id, 'code' => 'solution'])
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('auto_reviewed', $autoReviewed)
            ->assertJsonPath('message', $autoReviewed
                ? 'All test cases passed! Score: '.$maximum
                : 'Code submitted successfully. Awaiting review...');

        $submission = Submissions::firstOrFail();
        $this->assertSame($autoReviewed, $submission->is_score_submitted);
        $this->assertSame($autoReviewed, $submission->is_auto_reviewed);
        $this->assertSame($autoReviewed, $submission->scored_at !== null);
        $this->assertSame((float) ($autoReviewed ? $maximum : 0), (float) $submission->score);
        $this->assertCount($runs, $submission->test_case_results);
        foreach ($submission->test_case_results as $result) {
            $this->assertSame($passes, $result['passed']);
            $this->assertSame('hello', $result['expected_output']);
        }

        if (! $autoReviewed) {
            $this->actingAs(User::factory()->create(['user_type' => 'admin']))
                ->postJson('/admin/api/submissions/'.$submission->id.'/submit-score', [
                    'score' => $maximum,
                    'code_hash' => hash('sha256', 'solution'),
                ])->assertOk();
            $this->assertEquals($maximum, $submission->fresh()->score);
            $this->assertTrue($submission->fresh()->is_score_submitted);
            $this->assertNotNull($submission->fresh()->scored_at);
        }
    }

    public static function gradingCases(): array
    {
        return [
            'zero allocation' => [[0], 100, true, true, false],
            'partial allocation' => [[30, 40], 100, true, true, false],
            'full allocation' => [[40, 60], 100, true, true, true],
            'manual challenge' => [[], 100, true, true, false],
            'inactive language' => [[100], 100, false, true, false],
            'failed case' => [[100], 100, true, false, false],
            'fractional allocation' => [array_fill(0, 10, 0.1), 1, true, true, true],
            'fractional underallocation' => [[0.999999], 1, true, true, false],
            'score capped' => [[60, 60], 100, true, true, true],
        ];
    }
}
