<?php

namespace App\Providers;

use App\Models\Events;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $attributes = request()->attributes;
            if (! $attributes->has('active_event_title')) {
                $attributes->set('active_event_title', Events::where('isActive', true)->value('title') ?? 'Coding Competition');
            }
            $view->with('event_title', $attributes->get('active_event_title'));
        });
    }
}
