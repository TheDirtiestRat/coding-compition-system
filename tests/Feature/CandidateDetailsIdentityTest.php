<?php

namespace Tests\Feature;

use App\Models\Candidates;
use App\Models\Challenges;
use App\Models\Events;
use App\Models\Submissions;
use App\Models\User;
use Tests\TestCase;

class CandidateDetailsIdentityTest extends TestCase
{
    public function test_candidate_details_keep_the_selected_candidates_identity_and_submissions(): void
    {
        $event = Events::create(['title' => 'Competition']);
        $admin = User::factory()->create(['user_type' => 'admin']);
        $bobUser = User::factory()->create(['name' => 'bob', 'email' => 'bob@example.com']);
        $aliceUser = User::factory()->create(['name' => 'alice', 'email' => 'alice@example.com']);
        $bob = Candidates::create(['event_id' => $event->id, 'fullname' => 'Bob Candidate', 'group' => 'Blue', 'user_id' => $bobUser->id]);
        $alice = Candidates::create(['event_id' => $event->id, 'fullname' => 'Alice Candidate', 'group' => 'Red', 'user_id' => $aliceUser->id]);
        $bob->forceFill(['created_at' => now()])->save();
        $alice->forceFill(['created_at' => now()->subDay()])->save();

        $challenge = Challenges::create([
            'event_id' => $event->id,
            'title' => 'Bob Challenge',
            'description' => 'Solve it',
            'code_solution' => 'echo "solution";',
            'points' => 10,
            'difficulty' => 'easy',
            'language' => 'php',
        ]);
        Submissions::create([
            'candidate_id' => $bob->id,
            'challenge_id' => $challenge->id,
            'score' => 10,
            'code' => 'echo "Bob";',
        ]);

        $response = $this->actingAs($admin)->get('/admin/candidates/view/'.$bob->id);

        $response->assertOk()
            ->assertSee('Name: Bob Candidate')
            ->assertSee('Username: bob')
            ->assertSee('Email: bob@example.com')
            ->assertSee('Group: Blue')
            ->assertSee('Bob Challenge')
            ->assertDontSee('Name: Alice Candidate')
            ->assertSee('href="'.url('/admin/candidates/view/'.$bob->id).'"', false)
            ->assertSee('action="'.url('/admin/delete-candidate/'.$bob->id).'"', false)
            ->assertSee('data-id="'.$bob->id.'"', false)
            ->assertSee('href="'.url('/admin/candidates/view/'.$alice->id).'"', false)
            ->assertSee('action="'.url('/admin/delete-candidate/'.$alice->id).'"', false)
            ->assertSee('data-id="'.$alice->id.'"', false);
    }
}
