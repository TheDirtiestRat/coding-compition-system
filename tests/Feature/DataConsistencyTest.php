<?php

namespace Tests\Feature;

use App\Models\Candidates;
use App\Models\Events;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
}
