<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_and_toggle_are_unavailable(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', ['username' => 'example'])->assertNotFound();
        $this->actingAs(User::factory()->create(['user_type' => 'admin']))
            ->post('/admin/toggle-registration')->assertNotFound();
        $this->assertDatabaseMissing('users', ['name' => 'example']);
    }
}
