<?php

namespace Tests\Feature;

use App\Models\Challenges;
use App\Models\Events;
use App\Models\ProgrammingLanguage;
use App\Models\User;
use Tests\TestCase;

class ManualChallengeCasesTest extends TestCase
{
    private function challengeData(): array
    {
        $event = Events::create(['title' => 'Competition']);
        $language = ProgrammingLanguage::create([
            'name' => 'python',
            'run_command' => 'python {file}',
            'file_extension' => '.py',
            'is_active' => true,
        ]);

        return [
            'event_id' => $event->id,
            'title' => 'Manual challenge',
            'description' => 'Print hello.',
            'difficulty' => 'easy',
            'language' => $language->name,
            'language_id' => $language->id,
            'points' => 10,
            'code_solution' => 'print("hello")',
        ];
    }

    public function test_manual_challenge_can_be_created_without_test_cases(): void
    {
        $data = $this->challengeData();

        $this->actingAs(User::factory()->create(['user_type' => 'admin']))
            ->post('/admin/save-challenge', ['test_cases_present' => '1'] + $data)
            ->assertSessionHas('success');

        $this->assertDatabaseCount('challenges', 1);
        $this->assertSame(0, Challenges::firstOrFail()->testCases()->count());
    }

    public function test_explicit_empty_cases_clear_saved_cases_and_use_zero_case_points(): void
    {
        $data = $this->challengeData();
        $challenge = Challenges::create($data);
        $challenge->testCases()->create(['expected_output' => 'hello', 'points' => 10]);

        $this->actingAs(User::factory()->create(['user_type' => 'admin']))
            ->post('/admin/save-challenge', [
                'challenge_id' => $challenge->id,
                'test_cases_present' => '1',
                'points' => 1,
            ] + $data)->assertSessionHas('success');

        $this->assertSame(0, $challenge->testCases()->count());
        $this->assertEquals(1, $challenge->fresh()->points);
    }

    public function test_omitted_case_fields_preserve_existing_cases(): void
    {
        $data = $this->challengeData();
        $challenge = Challenges::create($data);
        $case = $challenge->testCases()->create(['expected_output' => 'hello', 'points' => 10]);

        $this->actingAs(User::factory()->create(['user_type' => 'admin']))
            ->post('/admin/save-challenge', ['challenge_id' => $challenge->id] + $data)
            ->assertSessionHas('success');

        $this->assertSame([$case->id], $challenge->testCases()->pluck('id')->all());
    }
}
