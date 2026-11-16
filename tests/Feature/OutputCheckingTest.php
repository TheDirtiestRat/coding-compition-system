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

class OutputCheckingTest extends TestCase
{
    private function challengeData(): array
    {
        $language = ProgrammingLanguage::firstOrCreate(['name' => 'python'], [
            'file_extension' => '.py', 'run_command' => 'python {file}', 'is_active' => true,
        ]);

        return [
            'event_id' => Events::create(['title' => 'Competition', 'isActive' => true])->id,
            'title' => 'Equivalent answers', 'description' => 'Print the answer',
            'difficulty' => 'easy', 'language' => 'python', 'language_id' => $language->id,
            'points' => 10, 'code_solution' => 'print(1)',
        ];
    }

    public function test_configuration_defaults_persistence_details_and_omitted_edit_fields(): void
    {
        $data = $this->challengeData();
        $this->actingAs(User::factory()->create(['user_type' => 'admin']))
            ->post('/admin/save-challenge', $data)->assertSessionHasNoErrors();
        $challenge = Challenges::firstOrFail();
        $this->assertSame('exact', $challenge->output_mode);
        $this->assertEquals(0.000001, $challenge->output_tolerance);

        $data['challenge_id'] = $challenge->id;
        $this->post('/admin/save-challenge', $data + ['output_mode' => 'numeric', 'output_tolerance' => '0.125'])
            ->assertSessionHasNoErrors();
        $this->getJson('/admin/api/get-challenge-details/'.$challenge->id)->assertOk()
            ->assertJsonPath('output_mode', 'numeric')->assertJsonPath('output_tolerance', 0.125);
        $this->post('/admin/save-challenge', $data)->assertSessionHasNoErrors();
        $this->assertSame('numeric', $challenge->fresh()->output_mode);
        $this->assertEquals(0.125, $challenge->fresh()->output_tolerance);
        $this->post('/admin/save-challenge', $data + ['output_tolerance' => 0])->assertSessionHasNoErrors();
        $this->assertSame('numeric', $challenge->fresh()->output_mode);
        $this->assertEquals(0, $challenge->fresh()->output_tolerance);
    }

    #[DataProvider('invalidConfigurations')]
    public function test_invalid_configuration_is_rejected(array $configuration, string $field): void
    {
        $this->actingAs(User::factory()->create(['user_type' => 'admin']))
            ->postJson('/admin/save-challenge', $this->challengeData() + $configuration)
            ->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->assertDatabaseCount('challenges', 0);
    }

    public static function invalidConfigurations(): array
    {
        return [
            [['output_mode' => 'custom'], 'output_mode'],
            [['output_mode' => 'numeric'], 'output_tolerance'],
            [['output_mode' => 'numeric', 'output_tolerance' => -0.1], 'output_tolerance'],
            [['output_mode' => 'numeric', 'output_tolerance' => 1.01], 'output_tolerance'],
            [['output_mode' => 'numeric', 'output_tolerance' => 'INF'], 'output_tolerance'],
            [['output_mode' => 'numeric', 'output_tolerance' => '1e999'], 'output_tolerance'],
            [['output_mode' => 'numeric', 'output_tolerance' => null], 'output_tolerance'],
        ];
    }

    #[DataProvider('gradingOutputs')]
    public function test_candidate_grading_uses_the_saved_checker(string $mode, string $actual, string $expected, bool $passes): void
    {
        $challenge = Challenges::create($this->challengeData() + ['output_mode' => $mode, 'output_tolerance' => 0.000001]);
        $challenge->testCases()->create(['expected_output' => $expected, 'points' => 10]);
        $codeOutputs = ['print(1 + 1)' => $actual, 'print(sum([1, 1]))' => $actual];
        $this->mock(CodeRunner::class)->shouldReceive('run')->twice()
            ->andReturnUsing(fn (string $code) => $codeOutputs[$code]);

        foreach (array_keys($codeOutputs) as $code) {
            $candidate = Candidates::create([
                'event_id' => $challenge->event_id, 'fullname' => 'Contestant',
                'user_id' => User::factory()->create()->id,
            ]);
            $this->withoutMiddleware(IsTimerStop::class)->actingAs($candidate->user)
                ->postJson('/api/submit-code-for-review', ['challenge_id' => $challenge->id, 'code' => $code])
                ->assertOk()->assertJsonPath('auto_reviewed', $passes)
                ->assertJsonMissingPath('test_case_results');
            $submission = Submissions::where('candidate_id', $candidate->id)->firstOrFail();
            $this->assertSame($passes, $submission->test_case_results[0]['passed']);
            $this->assertEquals($passes ? 10 : 0, $submission->score);
        }
    }

    public static function gradingOutputs(): array
    {
        return [
            ['exact', " 2\n", '2', true],
            ['exact', "2\n3", '2 3', false],
            ['tokens', "2\n3", '2 3', true],
            ['numeric', '2.0000005', '2', true],
            ['numeric', '2.01', '2', false],
            ['numeric', 'NaN', '2', false],
        ];
    }

    public function test_failed_draft_keeps_submitted_settings_and_saved_omitted_settings(): void
    {
        $data = $this->challengeData();
        $challenge = Challenges::create($data + ['output_mode' => 'numeric', 'output_tolerance' => 0.125]);
        $draft = array_replace($data, ['challenge_id' => $challenge->id, 'points' => 0]);
        $this->actingAs(User::factory()->create(['user_type' => 'admin']))
            ->from('/admin/challenges')->post('/admin/save-challenge', $draft)->assertSessionHasErrors('points');
        $html = $this->get('/admin/challenges')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/value="numeric" selected/', $html);
        $this->assertMatchesRegularExpression('/id="output_tolerance"[^>]*value="0.125"/', $html);

        $this->from('/admin/challenges')->post('/admin/save-challenge', $draft + [
            'output_mode' => 'tokens', 'output_tolerance' => '0.5',
        ])->assertSessionHasErrors('points')->assertSessionHasInput('output_mode', 'tokens');
        $html = $this->get('/admin/challenges')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/value="tokens" selected/', $html);
        $this->assertMatchesRegularExpression('/id="output_tolerance"[^>]*value="0.5"/', $html);
    }
}
