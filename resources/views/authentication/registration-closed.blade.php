<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registration Closed</title>

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
    </style>
</head>

<body>
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden max-w-md w-full p-8 text-center">
        <div class="size-24 rounded-full mx-auto flex items-center justify-center bg-gray-100 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                <path d="M12 8v4" />
                <path d="M12 16h.01" />
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Registration Closed</h1>
        <p class="text-gray-600 mb-6">Registration is currently closed. Please check back later or contact the administrator.</p>
        <div class="space-y-3">
            <a href="{{ url('/') }}" class="btn btn-primary w-full">Go Home</a>
            <a href="{{ url('/login') }}" class="btn btn-secondary w-full">Sign In</a>
        </div>
    </div>
</body>

</html>
