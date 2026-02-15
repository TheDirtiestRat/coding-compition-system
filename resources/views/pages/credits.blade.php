<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Credits</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body class="bg-base-200">
    @php
        $roleOrder = ['Guild Adviser', 'President', 'Vice President', 'Secretary', 'Member'];

        $members = [
            [
                'name' => 'Avery Stone',
                'role' => 'Guild Adviser',
                'age' => 29,
                'github' => 'https://github.com/avery-stone',
            ],
            [
                'name' => 'Blake Carter',
                'role' => 'President',
                'age' => 22,
                'github' => 'https://github.com/blake-carter',
            ],
            [
                'name' => 'Casey Rivera',
                'role' => 'Vice President',
                'age' => 21,
                'github' => '',
            ],
            [
                'name' => 'Drew Morgan',
                'role' => 'Secretary',
                'age' => 20,
                'github' => 'https://github.com/drew-morgan',
            ],
            [
                'name' => 'Emery Quinn',
                'role' => 'Member',
                'age' => 20,
                'github' => '',
            ],
            [
                'name' => 'Finley Reed',
                'role' => 'Member',
                'age' => 21,
                'github' => 'https://github.com/finley-reed',
            ],
            [
                'name' => 'Gray Harper',
                'role' => 'Member',
                'age' => 20,
                'github' => '',
            ],
            [
                'name' => 'Hayden Brooks',
                'role' => 'Member',
                'age' => 22,
                'github' => 'https://github.com/hayden-brooks',
            ],
            [
                'name' => 'Indigo Lane',
                'role' => 'Member',
                'age' => 19,
                'github' => '',
            ],
        ];

        $members = collect($members)
            ->sortBy(function ($member) use ($roleOrder) {
                return array_search($member['role'], $roleOrder, true);
            })
            ->values();
    @endphp

    <div class="p-3 container flex flex-col gap-3 mx-auto relative">
        <x-public-navbar active="credits" />

        <main class="space-y-3">
            <section class="card bg-base-300 rounded-box grid p-8 place-items-center text-center">
                <h1 class="text-4xl font-bold md:text-5xl">Programmers&apos; Guild Credits</h1>
                <p class="mx-auto mt-4 max-w-3xl text-lg text-base-content/80">
                    Meet the team behind the platform. Member photos can be replaced later, but for now each card uses
                    initials.
                </p>
            </section>

            <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($members as $member)
                    @php
                        $initials = collect(explode(' ', $member['name']))
                            ->filter()
                            ->map(function ($part) {
                                return strtoupper(substr($part, 0, 1));
                            })
                            ->take(2)
                            ->implode('');
                    @endphp

                    <article class="rounded-2xl bg-base-100 p-6 shadow border border-base-300">
                        <div
                            class="mx-auto mb-4 flex h-24 w-24 items-center justify-center rounded-3xl bg-neutral text-3xl font-extrabold text-neutral-content shadow">
                            {{ $initials }}
                        </div>

                        <h2 class="text-center text-2xl font-bold">{{ $member['name'] }}</h2>

                        <div class="mt-4 flex items-center justify-center gap-2">
                            <span class="badge badge-primary badge-soft">{{ $member['role'] }}</span>
                            <span class="badge badge-ghost">Age {{ $member['age'] }}</span>
                        </div>

                        @if (!empty($member['github']))
                            <a href="{{ $member['github'] }}" target="_blank" rel="noopener noreferrer"
                                class="btn btn-neutral mt-5 w-full rounded-xl">
                                GitHub
                            </a>
                        @endif
                    </article>
                @endforeach
            </section>
        </main>

        <footer class="footer footer-horizontal footer-center bg-neutral text-primary-content p-10 rounded-box mt-2">
            <aside>
                <svg width="50" height="50" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd"
                    clip-rule="evenodd" class="inline-block fill-current">
                    <path
                        d="M22.672 15.226l-2.432.811.841 2.515c.33 1.019-.209 2.127-1.23 2.456-1.15.325-2.148-.321-2.463-1.226l-.84-2.518-5.013 1.677.84 2.517c.391 1.203-.434 2.542-1.831 2.542-.88 0-1.601-.564-1.86-1.314l-.842-2.516-2.431.809c-1.135.328-2.145-.317-2.463-1.229-.329-1.018.211-2.127 1.231-2.456l2.432-.809-1.621-4.823-2.432.808c-1.355.384-2.558-.59-2.558-1.839 0-.817.509-1.582 1.327-1.846l2.433-.809-.842-2.515c-.33-1.02.211-2.129 1.232-2.458 1.02-.329 2.13.209 2.461 1.229l.842 2.515 5.011-1.677-.839-2.517c-.403-1.238.484-2.553 1.843-2.553.819 0 1.585.509 1.85 1.326l.841 2.517 2.431-.81c1.02-.33 2.131.211 2.461 1.229.332 1.018-.21 2.126-1.23 2.456l-2.433.809 1.622 4.823 2.433-.809c1.242-.401 2.557.484 2.557 1.838 0 .819-.51 1.583-1.328 1.847m-8.992-6.428l-5.01 1.675 1.619 4.828 5.011-1.674-1.62-4.829z">
                    </path>
                </svg>
                <p class="font-bold">
                    ACLC Programmers Guild.
                    <br />
                    Providing unreliable tech since 2024
                </p>
                <p>Copyright &copy; {{ date('Y') }} - All rights reserved.</p>
            </aside>
            <nav>
                <div class="grid grid-flow-col gap-4">
                    <a href="{{ url('/home') }}" class="link link-hover">Home</a>
                    <a href="{{ url('/home#about') }}" class="link link-hover">About</a>
                    <a href="{{ url('/credits') }}" class="link link-hover">Credits</a>
                    <a href="{{ url('/login') }}" class="link link-hover">Login</a>
                </div>
            </nav>
            <div>
                <p class="text-sm opacity-80">
                    1 Guild Adviser, 1 President, 1 Vice President, 1 Secretary, and 5 Members.
                </p>
            </div>
        </footer>
    </div>
</body>

</html>
