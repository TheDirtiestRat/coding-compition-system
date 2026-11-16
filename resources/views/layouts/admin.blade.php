<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <script src="{{ asset('js/admin-theme.js') }}"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('admin-title', 'Admin') | Coding Competition</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/admin.js'])
        @yield('admin-editor-assets')
    @endif
    @include('components.public-styles')
    @include('components.admin-styles')
</head>
<body class="admin-shell min-h-screen">
    <a href="#admin-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:bg-base-100 focus:p-3">Skip to content</a>
    @include('components.admin-side-bar')
    <div class="lg:pl-64">
        @include('components.admin-nav-bar')
        <main id="admin-content" class="min-w-0 p-4 sm:p-6" tabindex="-1">
            <p class="mb-5 max-w-2xl text-sm leading-6 admin-muted">@yield('admin-description')</p>
            @yield('admin-content')
        </main>
    </div>
    @include('components.alert-modal')
    @yield('admin-scripts')
</body>
</html>
