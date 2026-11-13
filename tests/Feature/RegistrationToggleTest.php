<?php

namespace Tests\Feature;

use App\Models\Events;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RegistrationToggleTest extends TestCase
{
    public function test_first_toggle_persists_open_registration_without_seeded_options(): void
    {
        Events::create(['title' => 'Competition', 'isActive' => true]);
        $admin = User::factory()->create(['user_type' => 'admin']);
        $this->assertDatabaseCount('options', 0);
        $this->get('/register')->assertViewIs('authentication.registration-closed');

        $this->actingAs($admin)->post('/admin/toggle-registration')
            ->assertRedirect('/admin/dashboard')
            ->assertSessionHas('success', 'Registration is now opened.');

        $this->assertDatabaseHas('options', [
            'option_name' => 'registration_open',
            'option_value' => '1',
            'option_datatype' => 'boolean',
        ]);
        $this->get('/register')->assertViewIs('authentication.register');
    }

    public function test_seeded_registration_can_close_and_reopen_without_changing_other_options(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        DB::table('options')->insert([
            ['option_name' => 'registration_open', 'option_value' => '1', 'option_datatype' => 'boolean'],
            ['option_name' => 'set_time', 'option_value' => '00:30:00', 'option_datatype' => 'text'],
        ]);
        $unrelated = DB::table('options')->where('option_name', 'set_time')->first();

        $this->actingAs($admin)->post('/admin/toggle-registration')
            ->assertRedirect('/admin/dashboard')
            ->assertSessionHas('success', 'Registration is now closed.');
        $this->assertDatabaseHas('options', ['option_name' => 'registration_open', 'option_value' => '0', 'option_datatype' => 'boolean']);

        $this->post('/admin/toggle-registration')
            ->assertRedirect('/admin/dashboard')
            ->assertSessionHas('success', 'Registration is now opened.');
        $this->assertDatabaseHas('options', ['option_name' => 'registration_open', 'option_value' => '1', 'option_datatype' => 'boolean']);
        $this->assertDatabaseCount('options', 2);
        $this->assertEquals($unrelated, DB::table('options')->where('option_name', 'set_time')->first());
    }
}
