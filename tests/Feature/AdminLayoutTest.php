<?php

namespace Tests\Feature;

use App\Models\Events;
use App\Models\User;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
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

    #[DataProvider('emptyFormPages')]
    public function test_empty_tables_and_validation_errors_render_together(string $page, string $message): void
    {
        $errors = (new ViewErrorBag)->put('default', new MessageBag(['title' => 'Please enter a title.']));

        $this->actingAs(User::factory()->create(['user_type' => 'admin']))
            ->withSession(['errors' => $errors])
            ->get('/admin/'.$page)
            ->assertOk()
            ->assertSee('Please enter a title.')
            ->assertSee($message);
    }

    public static function emptyFormPages(): array
    {
        return [
            ['events', 'No events yet.'],
            ['programming-languages', 'No languages yet.'],
        ];
    }

    public function test_timer_and_results_keep_their_standalone_displays(): void
    {
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));
        $this->get('/admin/countdown')->assertOk()->assertSee('id="countdown"', false)->assertSee('Back to overview');
        $this->get('/admin/results')->assertOk()->assertSee('No competitors in the active event yet.')->assertSee('Back to overview');
    }
}
