<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in | @include('components.title')</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @endif

    <style>
        a:focus-visible,
        button:focus-visible,
        input:focus-visible {
            outline: 3px solid #171717;
            outline-offset: 4px;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col bg-neutral-50 text-neutral-900 font-sans">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:block focus:bg-white focus:p-4">Skip to content</a>

    <header class="border-b border-neutral-200 bg-white">
        <nav aria-label="Main navigation" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 py-4 flex items-center justify-between gap-4">
            <a href="{{ url('/') }}" class="flex min-w-0 items-center gap-3 rounded-lg">
                <span class="w-9 h-9 shrink-0 rounded-xl bg-black flex items-center justify-center" aria-hidden="true">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                </span>
                <span class="text-base font-semibold">Coding competition</span>
            </a>
            <a href="{{ url('/') }}" class="shrink-0 rounded-lg px-3 py-3 text-sm font-medium hover:bg-neutral-100">Home</a>
        </nav>
    </header>

    <main id="main-content" tabindex="-1" class="flex-1 grid place-items-center px-4 py-10 sm:px-6 sm:py-14">
        <section aria-labelledby="login-title" class="w-full min-w-0 max-w-lg rounded-3xl border border-neutral-200 bg-white p-6 sm:p-8 shadow-sm">
            <p class="mb-3 text-sm text-neutral-600 wrap-anywhere">@include('components.title')</p>
            <h1 id="login-title" class="text-3xl font-medium tracking-tight">Log in to compete</h1>
            <p class="mt-3 text-sm leading-relaxed text-neutral-600">Enter your username and password to continue.</p>

            <form method="POST" action="{{ url('/login') }}" class="mt-8 space-y-6">
                @csrf

                @if (session('error'))
                    <div role="alert" class="rounded-xl border border-red-300 bg-red-50 p-4 text-sm text-red-800">
                        <p class="mb-1 font-semibold">Unable to log in</p>
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                <div>
                    <label for="username" class="mb-2 block text-sm font-medium">Username</label>
                    <input type="text" id="username" name="username" autocomplete="username" autocapitalize="none"
                        spellcheck="false" required value="{{ is_string(old('username')) ? old('username') : '' }}"
                        @error('username') aria-invalid="true" aria-describedby="username-error" @enderror
                        class="w-full min-h-14 rounded-xl border border-neutral-500 bg-white px-4 py-3 text-base text-neutral-900">
                    @error('username')
                        <p id="username-error" role="alert" class="mt-2 text-sm text-red-800">
                            <span class="font-semibold">Error:</span> {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-medium">Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" autocomplete="current-password" required
                            @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                            class="w-full min-h-14 rounded-xl border border-neutral-500 bg-white pl-4 pr-20 py-3 text-base text-neutral-900">
                        <button type="button" id="togglePassword" aria-controls="password" aria-label="Show password" aria-pressed="false"
                            class="absolute right-1 top-1 bottom-1 min-w-16 rounded-lg px-3 text-sm font-medium hover:bg-neutral-100">Show</button>
                    </div>
                    @error('password')
                        <p id="password-error" role="alert" class="mt-2 text-sm text-red-800">
                            <span class="font-semibold">Error:</span> {{ $message }}
                        </p>
                    @enderror
                </div>

                <button type="submit" class="w-full min-h-12 rounded-full bg-black px-6 py-3 font-semibold text-white hover:bg-neutral-800">Log in</button>
            </form>

            <p class="mt-8 border-t border-neutral-200 pt-6 text-sm text-neutral-600">
                Need an account? <a href="{{ url('/register') }}" class="rounded-sm font-semibold text-neutral-900 underline underline-offset-4">Register</a>
            </p>
        </section>
    </main>

    <footer class="px-6 pb-8 text-center text-sm leading-relaxed text-neutral-600">
        Need help signing in? Contact your competition organizer.
    </footer>

    <script>
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');

        togglePassword.addEventListener('click', function () {
            const showPassword = passwordInput.type === 'password';
            passwordInput.type = showPassword ? 'text' : 'password';
            togglePassword.textContent = showPassword ? 'Hide' : 'Show';
            togglePassword.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');
            togglePassword.setAttribute('aria-pressed', String(showPassword));
        });
    </script>
</body>

</html>
