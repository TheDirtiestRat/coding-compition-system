@props(['title'])

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <script src="{{ asset('js/admin-theme.js') }}"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} | Coding Competition</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @endif
    @include('components.public-styles')
    @include('components.admin-styles')
</head>
<body class="admin-shell min-h-screen flex flex-col">
    <a href="#admin-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:bg-base-100 focus:p-3">Skip to content</a>
    <header class="admin-chrome flex flex-wrap items-center justify-between gap-3 border-b border-base-300 px-4 py-3 sm:px-6">
        <a href="{{ url('/admin/dashboard') }}" class="btn btn-ghost">Back to overview</a>
        @include('components.admin-theme-toggle')
    </header>
    <main id="admin-content" tabindex="-1" class="flex-1 min-w-0 p-4 sm:p-6">
        {{ $slot }}
    </main>
    @stack('scripts')
</body>
</html>
