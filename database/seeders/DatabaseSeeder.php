<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Candidates;
use App\Models\Challenges;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin',
            'user_type' => 'admin',
            'email' => 'admin@example.com',
            'password' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => 'user',
        ]);

        // insert option values
        DB::table('options')->insert([
            'option_name' => 'start_compition',
            'option_datatype' => 'boolean',
            'option_value' => '0'
        ]);

        Candidates::factory(5)->create();

        Challenges::factory()->create([
            'title' => "Challenge A",
            'description' => 'Your goal is to create a "Hello world" program using Java script.',
            'points' => 10,
            'difficulty' => 'easy',
            'challege_key_file_path' => "master-files/JavaA.txt",
        ]);

        Challenges::factory()->create([
            'title' => "Challenge B",
            'description' => 'Create a program using Java script that adds value a = 10 and b = 5 and would show the sum of 15 Output: "The sum of A and B is = 15".',
            'points' => 15,
            'difficulty' => 'easy',
            'challege_key_file_path' => "master-files/JavaB.txt",
        ]);
    }
}
