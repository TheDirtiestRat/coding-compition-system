<?php

namespace Tests\Feature;

use App\Models\Candidates;
use App\Models\Challenges;
use App\Models\Events;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventAccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_event_removes_its_contestant_account_and_photos(): void
    {
        Storage::fake('public');
        $event = Events::create(['title' => 'Competition']);
        $user = User::factory()->create(['user_type' => 'user']);
        $this->candidate($event, $user);
        $challenge = $this->challengeWithPhoto($event);

        $this->actingAs(User::factory()->create(['user_type' => 'admin']))
            ->delete('/admin/delete-event/'.$event->id)
            ->assertRedirect('/admin/events')
            ->assertSessionHas('success', 'Event, related data, and associated contestant accounts deleted successfully. Accounts used by other events were preserved.');

        $this->assertDatabaseMissing('events', ['id' => $event->id]);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('candidates', ['event_id' => $event->id]);
        $this->assertDatabaseMissing('challenge_photos', ['challenge_id' => $challenge->id]);
        Storage::disk('public')->assertMissing('challenge-photos/deleted.png');
    }

    public function test_deleting_event_preserves_shared_accounts_admins_and_unrelated_users(): void
    {
        $event = Events::create(['title' => 'Deleted competition']);
        $otherEvent = Events::create(['title' => 'Kept competition']);
        $shared = User::factory()->create(['user_type' => 'user']);
        $admin = User::factory()->create(['user_type' => 'admin']);
        $otherAdmin = User::factory()->create(['user_type' => 'admin']);
        $unrelated = User::factory()->create(['user_type' => 'user']);
        foreach ([$shared, $admin, $otherAdmin] as $user) {
            $this->candidate($event, $user);
        }
        $otherCandidate = $this->candidate($otherEvent, $shared);

        $this->actingAs($admin)->delete('/admin/delete-event/'.$event->id)->assertSessionHas('success');

        foreach ([$shared, $admin, $otherAdmin, $unrelated] as $user) {
            $this->assertDatabaseHas('users', ['id' => $user->id]);
        }
        $this->assertDatabaseHas('candidates', ['id' => $otherCandidate->id]);
        $this->assertDatabaseMissing('candidates', ['event_id' => $event->id]);
    }

    public function test_failed_account_deletion_rolls_back_event_candidates_and_accounts(): void
    {
        Storage::fake('public');
        $event = Events::create(['title' => 'Competition']);
        $user = User::factory()->create(['user_type' => 'user']);
        $candidate = $this->candidate($event, $user);
        $challenge = $this->challengeWithPhoto($event);
        DB::statement("CREATE TRIGGER fail_event_account_delete BEFORE DELETE ON users BEGIN SELECT RAISE(ABORT, 'Account deletion failed'); END");

        try {
            $this->actingAs(User::factory()->create(['user_type' => 'admin']))
                ->delete('/admin/delete-event/'.$event->id)->assertStatus(500);

            $this->assertDatabaseHas('events', ['id' => $event->id]);
            $this->assertDatabaseHas('candidates', ['id' => $candidate->id]);
            $this->assertDatabaseHas('users', ['id' => $user->id]);
            $this->assertDatabaseHas('challenge_photos', ['challenge_id' => $challenge->id]);
            Storage::disk('public')->assertExists('challenge-photos/deleted.png');
        } finally {
            DB::statement('DROP TRIGGER fail_event_account_delete');
        }
    }

    private function candidate(Events $event, User $user): Candidates
    {
        return Candidates::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'fullname' => 'Contestant',
        ]);
    }

    private function challengeWithPhoto(Events $event): Challenges
    {
        $challenge = Challenges::create([
            'event_id' => $event->id,
            'title' => 'Hello',
            'description' => 'Print hello',
            'difficulty' => 'easy',
            'language' => 'python',
            'points' => 100,
            'code_solution' => 'print("hello")',
        ]);
        $challenge->photos()->create(['photo_path' => 'challenge-photos/deleted.png']);
        Storage::disk('public')->put('challenge-photos/deleted.png', 'photo');

        return $challenge;
    }
}
