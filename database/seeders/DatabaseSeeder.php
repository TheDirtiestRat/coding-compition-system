<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Candidates;
use App\Models\Challenges;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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

        Candidates::factory(10)->create();

        Challenges::factory()->create([
            'title' => "First Challenge",
            'description' => "Your goal is to create a Hello world program using python script.",
            'points' => 50,
            'difficulty' => 'easy',
            'challege_key_file_path' => "first_challenge_answer_key.txt",
        ]);

         Challenges::factory()->create([
            'title' => "Java Challenge",
            'description' => "Your goal is to create a Hello world program using Java script.",
            'points' => 50,
            'difficulty' => 'easy',
            'challege_key_file_path' => "java_challenge_answer_key.txt",
        ]);
    }
}
