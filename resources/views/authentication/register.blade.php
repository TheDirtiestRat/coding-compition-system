<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Register | Coding Competition</title>

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
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideIn {
            from { transform: translateX(-10px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
        .animate-slide-in { animation: slideIn 0.4s ease-out forwards; }
    </style>
</head>

<body>
    <div class="flex flex-col md:flex-row items-center justify-center gap-10 w-full max-w-6xl">
        <div class="text-white text-center md:text-left md:w-1/2 animate-slide-in">
            <div class="mb-6">
                <h1 class="text-5xl md:text-6xl font-bold mb-2">@include('components.title') | Coding Competition</h1>
                <p class="text-xl opacity-90">Join the competition. Show your skills.</p>
            </div>
        </div>

        <div class="animate-fade-in md:w-1/2 max-w-md w-full">
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div class="bg-black p-6 text-white">
                    <h2 class="text-3xl font-bold text-center">Create Account</h2>
                    <p class="opacity-90 text-center">Register to start coding</p>
                </div>

                <form method="POST" action="{{ url('/register') }}" class="p-8">
                    @csrf

                    @if ($errors->any())
                        <div id="alert_container" class="mb-6 animate-fade-in">
                            @foreach ($errors->all() as $error)
                                <div role="alert" class="alert alert-error flex items-center p-4 rounded-lg bg-red-50 border border-red-200 mb-2">
                                    <div class="flex-shrink-0">
                                        <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-red-800">{{ $error }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mb-6">
                        <label class="block text-gray-900 text-sm font-semibold mb-2" for="fullname">Full Name</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input type="text" id="fullname" name="fullname" placeholder="Enter your full name"
                                class="w-full pl-10 pr-4 py-3 border border-gray-700 rounded-lg focus:ring-2 bg-white text-gray-900 placeholder-gray-500 focus:ring-blue-900 focus:border-blue-500 transition duration-200 outline-none"
                                value="{{ old('fullname') }}" required />
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-900 text-sm font-semibold mb-2" for="username">Username</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input type="text" id="username" name="username" placeholder="Choose a username"
                                class="w-full pl-10 pr-4 py-3 border border-gray-700 rounded-lg focus:ring-2 bg-white text-gray-900 placeholder-gray-500 focus:ring-blue-900 focus:border-blue-500 transition duration-200 outline-none"
                                value="{{ old('username') }}" required />
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-900 text-sm font-semibold mb-2" for="password">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input type="password" id="password" name="password" placeholder="Choose a password (min 4 characters)"
                                class="w-full pl-10 pr-4 py-3 border border-gray-700 rounded-lg focus:ring-2 bg-white text-gray-900 placeholder-gray-500 focus:ring-blue-900 focus:border-blue-500 transition duration-200 outline-none" required minlength="4" />
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-black hover:from-gray-600 text-white font-semibold py-3 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-200 transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Register & Join
                    </button>

                    <div class="mt-6 text-center space-y-2">
                        <p class="text-gray-600 text-sm">
                            Already have an account?
                            <a href="{{ url('/login') }}" class="text-blue-600 hover:text-blue-800 font-medium">Sign in</a>
                        </p>
                        <a href="{{ url('/') }}"
                            class="inline-flex items-center text-gray-600 hover:text-gray-900 font-medium transition duration-200 text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Go Home
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
