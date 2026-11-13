<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Countdown</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body class="w-screen h-screen flex justify-center items-center">
    <div class="flex flex-col">
        <p class="p-0 text-center">@include('components.title')</p>
        <p id="countdown" class="text-[200px] font-semibold text-center p-0 leading-tight">
            00:00:00
        </p>
        <p class="p-0 text-center">Kompetition Ends in ...</p>
    </div>

    @include('components.countdown-script')
</body>

</html>
