<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Models\Candidates;
use App\Models\Challenges;
use App\Models\Events;
use App\Models\Submissions;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CandidateAccountDeletionTest extends TestCase
{
    public function test_deleting_only_candidate_cleans_up_account_and_submissions(): void
    {
        $candidate = $this->candidate(User::factory()->create(['user_type' => 'user']));
        $submission = $this->submission($candidate);

        $this->actingAs(User::factory()->create(['user_type' => 'admin']))
            ->delete('/admin/delete-candidate/'.$candidate->id)
            ->assertRedirect('/admin/candidates')->assertSessionHas('success');

        $this->assertDatabaseMissing('candidates', ['id' => $candidate->id]);
        $this->assertDatabaseMissing('users', ['id' => $candidate->user_id]);
        $this->assertDatabaseMissing('submissions', ['id' => $submission->id]);
        $this->assertDatabaseHas('challenges', ['id' => $submission->challenge_id]);
        $this->assertDatabaseHas('events', ['id' => $candidate->event_id]);
    }

    public function test_deleting_shared_candidate_preserves_other_event_entry_and_score(): void
    {
        $user = User::factory()->create(['user_type' => 'user']);
        $candidate = $this->candidate($user);
        $otherCandidate = $this->candidate($user);
        $submission = $this->submission($candidate);
        $otherSubmission = $this->submission($otherCandidate);

        $this->actingAs(User::factory()->create(['user_type' => 'admin']))
            ->delete('/admin/delete-candidate/'.$candidate->id)->assertSessionHas('success');

        $this->assertDatabaseMissing('candidates', ['id' => $candidate->id]);
        $this->assertDatabaseMissing('submissions', ['id' => $submission->id]);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseHas('candidates', ['id' => $otherCandidate->id]);
        $this->assertDatabaseHas('submissions', [
            'id' => $otherSubmission->id, 'score' => 75, 'is_score_submitted' => true,
        ]);
    }

    public function test_deleting_candidate_preserves_admin_accounts(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $otherAdmin = User::factory()->create(['user_type' => 'admin']);
        $this->actingAs($admin);

        foreach ([$admin, $otherAdmin] as $user) {
            $candidate = $this->candidate($user);
            $this->delete('/admin/delete-candidate/'.$candidate->id)->assertSessionHas('success');

            $this->assertDatabaseHas('users', ['id' => $user->id]);
            $this->assertDatabaseMissing('candidates', ['id' => $candidate->id]);
        }
    }

    public function test_account_cleanup_preserves_authenticated_user_even_if_not_admin(): void
    {
        $user = User::factory()->create(['user_type' => 'user']);
        $candidate = $this->candidate($user);
        $this->actingAs($user);

        app(AdminController::class)->delete_candidate((string) $candidate->id);

        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('candidates', ['id' => $candidate->id]);
    }

    public function test_failed_account_cleanup_rolls_back_candidate_and_scored_submission(): void
    {
        $candidate = $this->candidate(User::factory()->create(['user_type' => 'user']));
        $submission = $this->submission($candidate);
        DB::statement("CREATE TRIGGER fail_candidate_account_delete BEFORE DELETE ON users BEGIN SELECT RAISE(ABORT, 'Account deletion failed'); END");

        try {
            $this->actingAs(User::factory()->create(['user_type' => 'admin']))
                ->delete('/admin/delete-candidate/'.$candidate->id)->assertStatus(500);

            $this->assertDatabaseHas('users', ['id' => $candidate->user_id]);
            $this->assertDatabaseHas('candidates', ['id' => $candidate->id]);
            $this->assertDatabaseHas('submissions', [
                'id' => $submission->id, 'score' => 75, 'is_score_submitted' => true,
            ]);
        } finally {
            DB::statement('DROP TRIGGER fail_candidate_account_delete');
        }
    }

    private function candidate(User $user): Candidates
    {
        return Candidates::create([
            'event_id' => Events::create(['title' => 'Competition'])->id,
            'user_id' => $user->id,
            'fullname' => 'Contestant',
        ]);
    }

    private function submission(Candidates $candidate): Submissions
    {
        $challenge = Challenges::create([
            'event_id' => $candidate->event_id,
            'title' => 'Hello',
            'description' => 'Print hello',
            'difficulty' => 'easy',
            'language' => 'python',
            'points' => 100,
            'code_solution' => 'print("hello")',
        ]);

        return Submissions::create([
            'candidate_id' => $candidate->id,
            'challenge_id' => $challenge->id,
            'code' => 'print("hello")',
            'score' => 75,
            'is_score_submitted' => true,
        ]);
    }
}
