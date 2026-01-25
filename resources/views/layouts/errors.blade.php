<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Error</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body>
    <div class="bg-gray-300 p-4 h-screen w-screen flex justify-center items-center">
        <div class="flex flex-col gap-2 justify-center ">
            @yield('error-content')
            <a href="{{ url()->previous() }}" class="text-center">Go Back</a>
        </div>
    </div>
</body>

</html>
