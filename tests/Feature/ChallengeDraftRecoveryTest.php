<?php

namespace Tests\Feature;

use App\Models\Challenges;
use App\Models\Events;
use App\Models\ProgrammingLanguage;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChallengeDraftRecoveryTest extends TestCase
{
    public function test_failed_edit_restores_draft_and_resubmits_to_same_challenge(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['user_type' => 'admin']);
        Events::create(['title' => 'Active event', 'isActive' => true]);
        $event = Events::create(['title' => 'Selected event']);
        ProgrammingLanguage::create(['name' => 'java', 'run_command' => 'java {file}', 'file_extension' => '.java', 'is_active' => true]);
        $language = ProgrammingLanguage::create(['name' => 'python', 'run_command' => 'python {file}', 'file_extension' => '.py', 'is_active' => true]);
        $data = [
            'event_id' => $event->id,
            'title' => 'Existing challenge',
            'description' => 'Existing description',
            'difficulty' => 'easy',
            'language' => $language->name,
            'language_id' => $language->id,
            'points' => 10,
            'code_solution' => 'print(0)',
        ];
        $challenge = Challenges::create($data);
        $photo = $challenge->photos()->create(['photo_path' => 'challenge-photos/old.png']);
        $draft = array_replace($data, [
            'challenge_id' => (string) $challenge->id,
            'title' => 'Edited challenge',
            'description' => 'Edited description',
            'difficulty' => 'hard',
            'points' => '0',
            'code_solution' => "    print('</script>')\n\n",
            'delete_photos' => [(string) $photo->id],
            'test_cases' => [3 => ['input' => "  4\n", 'expected_output' => "  8\n", 'points' => '2.5']],
        ]);

        $this->actingAs($admin)->from('/admin/challenges')->post('/admin/save-challenge', $draft)
            ->assertRedirect('/admin/challenges')->assertSessionHasErrors('points')
            ->assertSessionHasInput('challenge_id', (string) $challenge->id)
            ->assertSessionHasInput('code_solution', $draft['code_solution'])
            ->assertSessionHasInput('test_cases.3.input', $draft['test_cases'][3]['input'])
            ->assertSessionHasInput('test_cases.3.expected_output', $draft['test_cases'][3]['expected_output']);

        $page = $this->get('/admin/challenges')->assertOk()
            ->assertSee('Update Challenge')->assertSee('Edited challenge')->assertSee('Edited description')
            ->assertSee('please select them again before saving.');
        $html = $page->getContent();
        $this->assertMatchesRegularExpression('/id="challenge_id"\s+value="'.$challenge->id.'"/', $html);
        $this->assertMatchesRegularExpression('/value="'.$event->id.'" selected>Selected event/', $html);
        $this->assertMatchesRegularExpression('/value="python" data-lang-id="'.$language->id.'" selected/', $html);
        $this->assertStringContainsString('challenge-photos', $html);
        $this->assertStringContainsString('const challengeDraft = JSON.parse(', $html);
        $this->assertStringNotContainsString("print('</script>')", $html);

        $this->post('/admin/save-challenge', array_replace($draft, ['points' => '10']))
            ->assertSessionHasNoErrors()->assertSessionHas('success', 'Challenge updated successfully!');
        $this->assertDatabaseCount('challenges', 1);
        $this->assertDatabaseHas('challenges', ['id' => $challenge->id, 'title' => 'Edited challenge', 'code_solution' => $draft['code_solution']]);
        $this->assertDatabaseMissing('challenge_photos', ['id' => $photo->id]);
        $this->assertDatabaseHas('test_cases', ['challenge_id' => $challenge->id, 'input' => "  4\n", 'expected_output' => "  8\n", 'points' => 2.5]);
    }
}
