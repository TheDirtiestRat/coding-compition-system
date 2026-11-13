<?php

namespace Tests\Feature;

use App\Models\Challenges;
use App\Models\Events;
use App\Models\ProgrammingLanguage;
use App\Models\User;
use Tests\TestCase;

class ProgrammingLanguageRenameTest extends TestCase
{
    public function test_rename_updates_only_linked_challenges_and_keeps_them_editable(): void
    {
        $language = ProgrammingLanguage::create([
            'name' => 'python',
            'compile_command' => null,
            'run_command' => 'python {file}',
            'file_extension' => '.py',
        ]);
        $otherLanguage = ProgrammingLanguage::create([
            'name' => 'java',
            'run_command' => 'java {file}',
            'file_extension' => '.java',
        ]);
        $data = [
            'event_id' => Events::create(['title' => 'Competition', 'isActive' => true])->id,
            'title' => 'Print hello',
            'description' => 'Print hello.',
            'difficulty' => 'easy',
            'language' => 'python',
            'language_id' => $language->id,
            'points' => 10,
            'code_solution' => 'print("hello")',
        ];
        $challenge = Challenges::create($data);
        $unlinked = Challenges::create(['language_id' => null] + $data);
        $otherLinked = Challenges::create(['language_id' => $otherLanguage->id] + $data);

        $this->actingAs(User::factory()->create(['user_type' => 'admin']))
            ->post('/admin/programming-languages/update', [
                'id' => $language->id,
                'name' => 'javascript',
                'compile_command' => null,
                'run_command' => $language->run_command,
                'file_extension' => $language->file_extension,
            ])->assertSessionHas('success');

        $details = $this->getJson('/admin/api/get-challenge-details/'.$challenge->id)
            ->assertOk()->assertJsonPath('language', 'javascript')->json();
        $languages = $this->getJson('/admin/api/get-languages')->assertOk()->json();
        $this->assertSame($details['language'], collect($languages)->firstWhere('id', $details['language_id'])['name']);
        $this->assertSame('python', $unlinked->fresh()->language);
        $this->assertSame('python', $otherLinked->fresh()->language);
        $this->assertSame($language->run_command, $language->fresh()->run_command);
        $this->assertSame($language->file_extension, $language->fresh()->file_extension);
        $this->assertNull($language->fresh()->compile_command);
        $this->assertSame($data['code_solution'], $challenge->fresh()->code_solution);
        $this->assertEquals($data['points'], $challenge->fresh()->points);

        $this->post('/admin/save-challenge', [
            'challenge_id' => $challenge->id,
            'language' => $details['language'],
            'title' => 'Edited after rename',
        ] + $data)->assertSessionHas('success');
        $this->assertSame('Edited after rename', $challenge->fresh()->title);
    }

    public function test_language_name_cannot_exceed_the_challenge_editor_limit(): void
    {
        $language = ProgrammingLanguage::create([
            'name' => 'python', 'run_command' => 'python {file}', 'file_extension' => '.py',
        ]);
        $this->actingAs(User::factory()->create(['user_type' => 'admin']))
            ->postJson('/admin/programming-languages/update', [
                'id' => $language->id,
                'name' => str_repeat('x', 51),
                'compile_command' => null,
                'run_command' => $language->run_command,
                'file_extension' => $language->file_extension,
            ])->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->assertSame('python', $language->fresh()->name);
    }
}
