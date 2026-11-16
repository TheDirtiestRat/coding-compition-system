<?php

namespace Tests\Feature;

use App\Models\Events;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminLayoutTest extends TestCase
{
    #[DataProvider('adminPages')]
    public function test_admin_pages_keep_the_shared_navigation_and_accessible_content(string $page, string $title): void
    {
        Events::create(['title' => 'Coding Challenge', 'isActive' => true]);

        $response = $this->actingAs(User::factory()->create(['user_type' => 'admin']))
            ->get('/admin/'.$page);

        $response->assertOk()
            ->assertSee('id="admin-content"', false)
            ->assertSee('tabindex="-1"', false)
            ->assertSee('Skip to content')
            ->assertSee('Admin menu')
            ->assertSee('admin-theme.js')
            ->assertSee($title)
            ->assertSee('aria-current="page"', false)
            ->assertSee('href="'.url('/admin/results').'"', false)
            ->assertSee('href="'.url('/admin/programming-languages').'"', false);
    }

    public static function adminPages(): array
    {
        return [
            ['dashboard', 'Overview'],
            ['events', 'Event details'],
            ['candidates', 'Competitors'],
            ['challenges', 'Challenges'],
            ['submissions', 'Submissions'],
            ['programming-languages', 'Languages'],
        ];
    }
}
