<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class CandidatesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fullname = fake()->name();
        $user = User::factory()->create([
                'name' => strtolower($fullname),
                'email' => trim($fullname).'@hungergame.com',
                'password' => 'hungergames',
        ]);
        return [
            'fullname' => $fullname,
            'group' => "none",
            'user_id' => $user->id,
        ];
    }
}
