<?php

namespace Tests\Feature;

use App\Models\Candidates;
use App\Models\Events;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CandidateUsernameTest extends TestCase
{
    public function test_admin_cannot_create_candidate_with_another_users_login_name(): void
    {
        $event = Events::create(['title' => 'Competition']);
        $admin = User::factory()->create(['name' => 'Admin', 'email' => 'admin@example.com', 'user_type' => 'admin']);

        $this->actingAs($admin)->post('/admin/add-candidate', [
            'event_id' => $event->id,
            'candidate_name' => 'New Candidate',
            'username' => ' Admin ',
            'user_password' => 'password',
        ])->assertSessionHasErrors('username');

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('candidates', 0);
    }

    public function test_admin_cannot_rename_candidate_to_another_users_login_name(): void
    {
        $event = Events::create(['title' => 'Competition']);
        $admin = User::factory()->create(['name' => 'Admin', 'email' => 'admin@example.com', 'user_type' => 'admin']);
        $user = User::factory()->create(['name' => 'competitor']);
        $candidate = Candidates::create(['event_id' => $event->id, 'fullname' => 'Original Name', 'group' => 'none', 'user_id' => $user->id]);

        $this->actingAs($admin)->post('/admin/update-candidate', [
            'event_id' => $event->id,
            'candidate_id' => $candidate->id,
            'edit_name' => 'Edited Name',
            'edit_username' => ' Admin ',
            'edit_user_password' => 'newpassword',
        ])->assertSessionHasErrors('edit_username');

        $this->assertSame('competitor', $user->fresh()->name);
        $this->assertSame('Original Name', $candidate->fresh()->fullname);
    }

    public function test_admin_can_keep_own_username_while_editing_name_and_password(): void
    {
        $event = Events::create(['title' => 'Competition']);
        $admin = User::factory()->create(['user_type' => 'admin']);
        $user = User::factory()->create(['name' => 'competitor', 'email' => 'competitor@hungergame.com']);
        $candidate = Candidates::create(['event_id' => $event->id, 'fullname' => 'Original Name', 'group' => 'none', 'user_id' => $user->id]);

        $this->actingAs($admin)->post('/admin/update-candidate', [
            'event_id' => $event->id,
            'candidate_id' => $candidate->id,
            'edit_name' => 'Edited Name',
            'edit_username' => ' competitor ',
            'edit_user_password' => 'newpassword',
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertSame('competitor', $user->fresh()->name);
        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password));
        $this->assertSame('Edited Name', $candidate->fresh()->fullname);
    }
}
