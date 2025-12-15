<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body>
    <div class="p-3 container flex flex-col gap-3 mx-auto w-full">
        <h1 class="mx-auto text-4xl">Coding Compition</h1>

        {{-- login form --}}
        <form method="POST" action="{{ url('/login') }}">
            @csrf
            <fieldset class="fieldset bg-base-200 border-base-300 rounded-xl w-xs shadow border p-4 mx-auto">
                <legend class="fieldset-legend text-2xl">Login</legend>
                {{-- alert --}}
                @if (session('error'))
                    <div id="alert_container" class="w-full flex-col gap-1">
                        <div role="alert" id="alert_error" class="alert alert-error">
                            <span>{{ session('error') }}.</span>
                        </div>
                    </div>
                @endif

                <label class="label">Username</label>
                <input type="text" class="input" name="username" placeholder="Username" />
                @error('username')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror

                <label class="label">Password</label>
                <input type="password" class="input" name="password" placeholder="Password" />
                @error('password')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror

                <button type="submit" class="btn btn-neutral mt-4 w-full">Login</button>

                <div class="flex flex-row justify-center mt-3">
                    <a href="{{ url('/') }}" class="link">Go Home</a>
                </div>
            </fieldset>
        </form>
    </div>
</body>

</html>
