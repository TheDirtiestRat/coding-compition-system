<?php

namespace Tests\Feature;

use App\Models\Candidates;
use App\Models\Challenges;
use App\Models\Events;
use App\Models\ProgrammingLanguage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DataConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_candidate_creation_rolls_back_registered_user(): void
    {
        Events::create(['title' => 'Competition', 'isActive' => true]);
        DB::table('options')->insert([
            'option_name' => 'registration_open',
            'option_datatype' => 'boolean',
            'option_value' => '1',
        ]);
        Candidates::creating(function () {
            throw new \RuntimeException('Candidate save failed.');
        });

        try {
            $this->post('/register', [
                'fullname' => 'New Candidate',
                'username' => 'newcandidate',
                'password' => 'password',
            ])->assertSessionHasErrors('error');

            $this->assertDatabaseMissing('users', ['name' => 'newcandidate']);
        } finally {
            Candidates::flushEventListeners();
        }
    }

    public function test_admin_candidate_creation_does_not_need_factory_defaults(): void
    {
        $event = Events::create(['title' => 'Competition']);
        $admin = User::factory()->create(['user_type' => 'admin']);

        $this->actingAs($admin)->post('/admin/add-candidate', [
            'event_id' => $event->id,
            'candidate_name' => 'New Candidate',
            'username' => 'newcandidate',
            'user_password' => 'password',
        ])->assertSessionHas('success');

        $user = User::where('name', 'newcandidate')->firstOrFail();
        $this->assertSame('user', $user->user_type);
        $this->assertDatabaseHas('candidates', ['user_id' => $user->id, 'event_id' => $event->id]);
    }

    private function challenge_data(): array
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
            'title' => 'Original challenge',
            'description' => 'Print hello.',
            'difficulty' => 'easy',
            'language' => $language->name,
            'language_id' => $language->id,
            'points' => 10,
            'code_solution' => 'print("hello")',
        ];
    }

    public function test_new_challenges_get_distinct_database_ids(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $data = $this->challenge_data();

        $this->actingAs($admin)->post('/admin/save-challenge', ['challenge_id' => ''] + $data)
            ->assertSessionHas('success');
        $this->post('/admin/save-challenge', ['challenge_id' => '', 'title' => 'Second challenge'] + $data)
            ->assertSessionHas('success');

        $this->assertDatabaseCount('challenges', 2);
        $this->assertDatabaseHas('challenges', ['title' => 'Original challenge']);
        $this->assertDatabaseHas('challenges', ['title' => 'Second challenge']);
    }

    public function test_failed_challenge_deletion_preserves_photo_file(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['user_type' => 'admin']);
        $challenge = Challenges::create($this->challenge_data());
        Storage::disk('public')->put('challenge-photos/example.png', 'photo');
        $challenge->photos()->create(['photo_path' => 'challenge-photos/example.png']);
        Challenges::deleting(function () {
            throw new \RuntimeException('Deletion failed.');
        });

        try {
            $this->actingAs($admin)->delete('/admin/delete-challenge/'.$challenge->id)->assertStatus(500);

            $this->assertDatabaseHas('challenges', ['id' => $challenge->id]);
            Storage::disk('public')->assertExists('challenge-photos/example.png');
        } finally {
            Challenges::flushEventListeners();
        }
    }
}
