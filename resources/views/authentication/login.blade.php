<x-public-layout title="Log in" active="login">
    <section aria-labelledby="login-title" class="max-w-lg mx-auto px-6 py-10 sm:py-14">
        <div class="motion-enter rounded-3xl border border-neutral-200 bg-white p-6 sm:p-8 shadow-sm">
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
        </div>
    </section>

    @push('scripts')
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
    @endpush
</x-public-layout>
