<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login | Coding Competition</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #121215d0 0%, #96979b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Custom animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                transform: translateX(-10px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }

        .animate-slide-in {
            animation: slideIn 0.4s ease-out forwards;
        }
    </style>
</head>

<body>
    <div class="flex flex-col md:flex-row items-center justify-center gap-10 w-full max-w-6xl">
        <!-- Left side - Branding/Visual -->
        <div class="text-white text-center md:text-left md:w-1/2 animate-slide-in">
            <div class="mb-6">
                <h1 class="text-5xl md:text-6xl font-bold mb-2">@include('components.title') | Coding Competition</h1>
                <p class="text-xl opacity-90">Showcase your skills. Challenge your limits. And Conquer.</p>
            </div>

            <div class="hidden md:block mt-10">
                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Real-time Challenges</h3>
                        <p class="text-white/80">Test your skills with live coding problems</p>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                            class="w-6 h-6 text-white fill-current">

                            <!-- Taller, thinner cup -->
                            <path d="M7 3h10v4
                                            C17 11 14.5 13 12 13
                                            C9.5 13 7 11 7 7V3z" />

                            <!-- Left slim bull-horn handle -->
                            <path d="M7 5
                                            C4.5 5 4 9 6.8 10
                                            C7.6 10.3 8 9.4 7.6 8.8
                                            C7.2 8.2 7 7.6 7 6.8
                                            C7 6.2 7 5.6 7 5z" />

                            <!-- Right slim bull-horn handle -->
                            <path d="M17 5
           C19.5 5 20 9 17.2 10
           C16.4 10.3 16 9.4 16.4 8.8
           C16.8 8.2 17 7.6 17 6.8
           C17 6.2 17 5.6 17 5z" />

                            <!-- Slightly longer stem -->
                            <rect x="10.75" y="13" width="2.5" height="3.5" rx="1" />

                            <!-- Slim base -->
                            <rect x="8.5" y="16.5" width="7" height="1.8" rx="1" />
                            <rect x="7.5" y="18.5" width="9" height="2" rx="1" />

                        </svg>

                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Competitive Environment</h3>
                        <p class="text-white/80">Test your skills with other competitors</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right side - Login Form -->
        <div class="animate-fade-in md:w-1/2 max-w-md w-full">
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <!-- Form Header -->
                <div class="bg-black p-6 text-white">
                    <h2 class="text-3xl font-bold text-center">Welcome Back</h2>
                    <p class="opacity-90 text-center">Sign in to your account to continue</p>
                </div>

                <!-- Login Form -->
                <form method="POST" action="{{ url('/login') }}" class="p-8">
                    @csrf

                    <!-- Alert Messages -->
                    @if (session('error'))
                        <div id="alert_container" class="mb-6 animate-fade-in">
                            <div role="alert" id="alert_error"
                                class="alert alert-error flex items-center p-4 rounded-lg bg-red-50 border border-red-200">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Username Field -->
                    <div class="mb-6">
                        <label class="block text-gray-900 text-sm font-semibold mb-2" for="username">
                            Username or Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input type="text" id="username" name="username" placeholder="Enter your username or email"
                                class="w-full pl-10 pr-4 py-3 border border-gray-700 rounded-lg focus:ring-2 bg-white text-gray-900 placeholder-gray-500 focus:ring-blue-900 focus:border-blue-500 transition duration-200 outline-none"
                                value="{{ old('username') }}" />
                        </div>
                        @error('username')
                            <div class="text-red-500 text-sm mt-1 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Password Field with Show/Hide Toggle -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-gray-900 text-sm font-semibold" for="password">
                                Password
                            </label>
                            <button type="button" id="togglePassword"
                                class="text-sm text-gray-600 hover:text-gray-700 flex items-center gap-1">
                            </button>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input type="password" id="password" name="password" placeholder="Enter your password"
                                class="w-full pl-10 pr-12 py-3 border border-gray-700 rounded-lg focus:ring-2 bg-white text-gray-900 placeholder-gray-500 focus:ring-blue-900 focus:border-blue-700 transition duration-200 outline-none" />
                            <!-- Eye icon inside password field -->
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <button type="button" id="passwordToggle"
                                    class="text-gray-600 hover:text-gray-600 focus:outline-none">
                                    <svg id="passwordEyeIcon" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @error('password')
                            <div class="text-red-500 text-sm mt-1 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Login Button -->
                    <button type="submit"
                        class="w-full  bg-black hover:from-gray-600 text-white font-semibold py-3 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-200 transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Sign In
                    </button>

                    <!-- Home Link -->
                    <div class="mt-6 text-center">
                        <a href="{{ url('/') }}"
                            class="inline-flex items-center text-gray-600 hover:text-gray-900 font-medium transition duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                </path>
                            </svg>
                            Go Home
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Get password toggle elements
            const passwordInput = document.getElementById('password');
            const togglePasswordBtn = document.getElementById('togglePassword');
            const passwordToggleBtn = document.getElementById('passwordToggle');
            const toggleText = document.getElementById('toggleText');
            const eyeIcon = document.getElementById('eyeIcon');
            const passwordEyeIcon = document.getElementById('passwordEyeIcon');

            let isPasswordVisible = false;

            // Function to toggle password visibility
            function togglePasswordVisibility() {
                isPasswordVisible = !isPasswordVisible;

                if (isPasswordVisible) {
                    // Show password
                    passwordInput.type = 'text';
                    toggleText.textContent = 'Hide';

                    // Update eye icons to "eye slash" (hidden state)
                    eyeIcon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    `;

                    passwordEyeIcon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    `;
                } else {
                    // Hide password
                    passwordInput.type = 'password';
                    toggleText.textContent = 'Show';

                    // Update eye icons to "eye" (visible state)
                    eyeIcon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    `;

                    passwordEyeIcon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    `;
                }

                // Focus back on password input for better UX
                passwordInput.focus();
            }

            // Add event listeners to both toggle buttons
            togglePasswordBtn.addEventListener('click', togglePasswordVisibility);
            passwordToggleBtn.addEventListener('click', togglePasswordVisibility);

            // Also allow toggling by pressing Enter on the toggle button
            togglePasswordBtn.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    togglePasswordVisibility();
                }
            });

            // Add focus effects to form inputs
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                // Add focus effect
                input.addEventListener('focus', function () {
                    this.parentElement.classList.add('ring-2', 'ring-blue-100');
                });

                // Remove focus effect
                input.addEventListener('blur', function () {
                    this.parentElement.classList.remove('ring-2', 'ring-blue-100');
                });
            });

            // Add smooth transitions for error messages
            const errorMessages = document.querySelectorAll('.text-red-500');
            errorMessages.forEach(error => {
                error.classList.add('animate-fade-in');
            });
        });
    </script>
</body>

</html>