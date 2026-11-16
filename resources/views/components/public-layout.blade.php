@props(['title', 'active'])

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} | @include('components.title')</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @endif

    @include('components.public-styles')
    @if ($active === 'home')
        <script src="{{ asset('js/homepage.js') }}" defer></script>
    @endif
</head>
<body class="min-h-screen flex flex-col">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:block focus:bg-white focus:p-4 focus:text-neutral-900">Skip to content</a>
    <x-public-navbar :active="$active" />

    <main id="main-content" class="flex-1" tabindex="-1">
        {{ $slot }}
    </main>

    <footer class="border-t border-neutral-200">
        <div class="max-w-7xl mx-auto px-6 lg:px-12 py-8 flex flex-wrap items-center justify-between gap-5">
            <div>
                <p class="font-semibold">Coding Competition</p>
                <p class="text-sm text-neutral-500 mt-2">ACLC Programmers' Guild · &copy; {{ date('Y') }}</p>
            </div>
            <nav aria-label="Footer navigation" class="flex flex-wrap gap-2 text-sm text-neutral-600">
                <a href="{{ $active === 'home' ? '#about' : url('/#about') }}" class="px-3 py-3 rounded-lg hover:text-neutral-900">How to play</a>
                <a href="{{ url('/credits') }}" class="px-3 py-3 rounded-lg hover:text-neutral-900">Meet the team</a>
            </nav>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
