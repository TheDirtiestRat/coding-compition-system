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

class PrivateGradingFeedbackTest extends TestCase
{
    #[DataProvider('gradingOutcomes')]
    public function test_private_results_stay_available_only_to_admins(string $outcome): void
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
            'is_active' => true,
        ]);
        $challenge = Challenges::create([
            'event_id' => $candidate->event_id,
            'title' => 'Private grading',
            'description' => 'Solve the problem',
            'difficulty' => 'easy',
            'language' => 'python',
            'language_id' => $language->id,
            'points' => 100,
            'code_solution' => 'reference solution',
        ]);
        $testCase = $challenge->testCases()->create([
            'input' => 'private-input-marker',
            'expected_output' => 'private-answer-marker',
            'points' => 100,
        ]);
        $output = $outcome === 'passed' ? $testCase->expected_output : 'private-output-marker';
        $error = $outcome === 'error' ? 'private-server-error-marker' : null;
        $this->mock(CodeRunner::class)->shouldReceive('run')->once()
            ->with('submitted solution', \Mockery::type(ProgrammingLanguage::class), $testCase->input, 5)
            ->andReturnUsing(function () use ($output, $error) {
                if ($error !== null) {
                    throw new \RuntimeException($error);
                }

                return $output;
            });

        $passed = $outcome === 'passed';
        $this->withoutMiddleware(IsTimerStop::class)->actingAs($candidate->user)
            ->postJson('/api/submit-code-for-review', [
                'challenge_id' => $challenge->id,
                'code' => 'submitted solution',
            ])->assertOk()->assertExactJson([
                'status' => 'success',
                'message' => $passed ? 'All test cases passed! Score: 100' : 'Code submitted successfully. Awaiting review...',
                'auto_reviewed' => $passed,
            ]);

        $submission = Submissions::firstOrFail();
        $expectedResults = [[
            'test_case_id' => $testCase->id,
            'input' => $testCase->input,
            'expected_output' => $testCase->expected_output,
            'points' => 100,
            'passed' => $passed,
            'actual_output' => $error === null ? $output : null,
            'error' => $error,
        ]];
        $this->assertSame($expectedResults, $submission->test_case_results);
        $this->assertSame($passed, $submission->is_score_submitted);
        $this->assertSame($passed, $submission->is_auto_reviewed);
        $this->assertEquals($passed ? 100 : 0, $submission->score);

        $this->get('/code-editor/'.$challenge->id)->assertOk()
            ->assertViewHas('details', fn ($details) => ! array_key_exists('test_case_results', $details->getAttributes()))
            ->assertSee('submitted solution')
            ->assertDontSee('private-input-marker')
            ->assertDontSee('private-answer-marker')
            ->assertDontSee('private-output-marker')
            ->assertDontSee('private-server-error-marker');

        $adminUrl = '/admin/api/submissions/'.$submission->id;
        $this->getJson($adminUrl)->assertForbidden();
        $this->actingAs(User::factory()->create(['user_type' => 'admin']))
            ->getJson($adminUrl)->assertOk()
            ->assertJsonPath('submission.test_case_results', $expectedResults);
    }

    public static function gradingOutcomes(): array
    {
        return [['passed'], ['failed'], ['error']];
    }
}
