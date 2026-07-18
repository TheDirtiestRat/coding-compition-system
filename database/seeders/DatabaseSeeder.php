<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'user_type' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('dunhillleal24'),
        ]);

        DB::table('options')->insert([
            'option_name' => 'start_compition',
            'option_datatype' => 'boolean',
            'option_value' => '0'
        ]);
        DB::table('options')->insert([
            'option_name' => 'set_time',
            'option_datatype' => 'text',
            'option_value' => '00:00:00'
        ]);
        DB::table('options')->insert([
            'option_name' => 'expected_end_time',
            'option_datatype' => 'text',
            'option_value' => '00:00:00'
        ]);
        DB::table('options')->insert([
            'option_name' => 'registration_open',
            'option_datatype' => 'boolean',
            'option_value' => '0'
        ]);

        $this->call([
            ChallengeSeeder::class,
            ProgrammingLanguageSeeder::class,
        ]);
    }
}
