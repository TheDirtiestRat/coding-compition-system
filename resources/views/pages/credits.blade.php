<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credits | @include('components.title')</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @endif

    <style>
        a:focus-visible {
            outline: 3px solid #171717;
            outline-offset: 4px;
        }

        main[id] {
            scroll-margin-top: 11rem;
        }

        @media (min-width: 640px) {
            main[id] {
                scroll-margin-top: 7rem;
            }
        }
    </style>
</head>

<body class="min-h-screen flex flex-col bg-white text-neutral-900 font-sans">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:block focus:bg-white focus:p-4">Skip to content</a>

    @php
        $roleOrder = ['Guild Elder', 'President', 'Vice President', 'Secretary', 'Guild Member'];

        $members = [
            [
                'name' => 'Dunhill Leal',
                'role' => 'Guild Elder',
                'age' => 'Secret',
                'github' => 'https://github.com/TheDirtiestRat',
                'photo' => 'images/guild/dunhill.png',
            ],
            [
                'name' => 'Miko Carlo A. Arar',
                'role' => 'President',
                'age' => 23,
                'github' => 'https://github.com/mekowkowwe',
                'photo' => 'images/guild/miko.jpg',
            ],
            [
                'name' => 'Mark James V. Felecio',
                'role' => 'Vice President',
                'age' => 19,
                'github' => 'https://github.com/mjfelecio',
                'photo' => 'images/guild/mark.png',
            ],
            [
                'name' => 'Avelino Magallanes',
                'role' => 'Secretary',
                'age' => 20,
                'github' => 'https://github.com/The-Grim-Reaper110011101',
                'photo' => 'images/guild/ave.png',
            ],
            [
                'name' => 'Dj Lynn Marie J. Lanuza',
                'role' => 'Guild Member',
                'age' => 22,
                'github' => 'https://github.com/dejayy27',
                'photo' => 'images/guild/dj.png',
            ],
            [
                'name' => 'Justine Dorae L. Suralta',
                'role' => 'Guild Member',
                'age' => 20,
                'github' => 'https://github.com/Coffleatea',
                'photo' => 'images/guild/justine.png',
            ],
            [
                'name' => 'Jeson Beltran',
                'role' => 'Guild Member',
                'age' => 20,
                'github' => 'https://github.com/Katsura6',
                'photo' => 'images/guild/jeson.png',
            ],
            [
                'name' => 'John Kenneth D. Dignos',
                'role' => 'Guild Member',
                'age' => 21,
                'github' => 'https://github.com/IceNinja2017',
                'photo' => 'images/guild/kenneth.png',
            ],
            [
                'name' => 'John Ashly O. Gasacao',
                'role' => 'Guild Member',
                'age' => 20,
                'github' => 'https://github.com/OfficialSage26',
                'photo' => 'images/guild/ashly.png',
            ],
        ];

        $members = collect($members)
            ->sortBy(function ($member) use ($roleOrder) {
                return array_search($member['role'], $roleOrder, true);
            })
            ->values();

        $tiers = [
            [
                'title' => 'Guild Elder',
                'members' => $members->where('role', 'Guild Elder')->values(),
                'grid' => 'max-w-sm grid-cols-1',
            ],
            [
                'title' => 'Officers',
                'members' => $members->whereIn('role', ['President', 'Vice President', 'Secretary'])->values(),
                'grid' => 'max-w-5xl grid-cols-1 md:grid-cols-3',
            ],
            [
                'title' => 'Guild Members',
                'members' => $members->where('role', 'Guild Member')->values(),
                'grid' => 'max-w-5xl grid-cols-1 sm:grid-cols-2',
            ],
        ];
    @endphp

    <x-public-navbar active="credits" />

    <main id="main-content" tabindex="-1" class="w-full flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 pt-10 pb-12 sm:pt-14">
        <div class="max-w-2xl mb-10 sm:mb-14">
            <p class="mb-3 text-sm text-neutral-600">ACLC Programmers' Guild</p>
            <h1 class="text-4xl sm:text-5xl font-medium tracking-tight">Meet the guild.</h1>
            <p class="mt-4 text-base leading-relaxed text-neutral-600">The people behind the coding competition platform.</p>
        </div>

        <div class="space-y-10 sm:space-y-14">
            @foreach ($tiers as $tier)
                <section aria-labelledby="tier-heading-{{ $loop->index }}">
                    <h2 id="tier-heading-{{ $loop->index }}" class="mb-6 text-center text-xl font-medium tracking-tight">
                        {{ $tier['title'] }}
                    </h2>

                    <div class="grid gap-5 w-full mx-auto {{ $tier['grid'] }}">
                        @foreach ($tier['members'] as $member)
                            @php
                                $isLastOddMember =
                                    $tier['title'] === 'Guild Members' &&
                                    $tier['members']->count() % 2 === 1 &&
                                    $loop->last;

                                $initials = collect(explode(' ', $member['name']))
                                    ->filter()
                                    ->map(function ($part) {
                                        return strtoupper(substr($part, 0, 1));
                                    })
                                    ->take(2)
                                    ->implode('');
                            @endphp

                            <article @class([
                                'min-w-0 rounded-3xl border border-neutral-200 bg-neutral-50 p-6 text-center',
                                'sm:col-span-2 sm:justify-self-center sm:w-1/2' => $isLastOddMember,
                            ])>
                                @if (!empty($member['photo']) && file_exists(public_path($member['photo'])))
                                    <img src="{{ asset($member['photo']) }}" alt="{{ $member['name'] }}" loading="lazy" width="96" height="96"
                                        class="mx-auto h-24 w-24 rounded-2xl border border-neutral-200 object-cover">
                                @else
                                    <div aria-hidden="true" class="mx-auto flex h-24 w-24 items-center justify-center rounded-2xl bg-neutral-200 text-3xl font-medium">
                                        {{ $initials }}
                                    </div>
                                @endif

                                <h3 class="mt-4 text-xl font-medium tracking-tight wrap-anywhere">{{ $member['name'] }}</h3>
                                <p class="mt-3 flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-sm text-neutral-600">
                                    <span>{{ $member['role'] }}</span>
                                    <span>Age {{ $member['age'] }}</span>
                                </p>

                                @if (!empty($member['github']))
                                    <a href="{{ $member['github'] }}" target="_blank" rel="noopener noreferrer"
                                        aria-label="{{ $member['name'] }} on GitHub (opens in a new tab)"
                                        class="mt-5 inline-flex min-h-11 items-center justify-center gap-2 rounded-full border border-neutral-300 bg-white px-5 py-2 text-sm font-medium hover:bg-neutral-100">
                                        GitHub
                                        <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 5h6v6m0-6L9 15M19 13v5a1 1 0 01-1 1H6a1 1 0 01-1-1V6a1 1 0 011-1h5" />
                                        </svg>
                                    </a>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </main>

    <footer class="border-t border-neutral-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 py-8 flex flex-wrap items-center justify-between gap-5">
            <div>
                <p class="font-semibold">Coding competition</p>
                <p class="mt-2 text-sm text-neutral-600">ACLC Programmers' Guild &middot; &copy; {{ date('Y') }}</p>
            </div>
            <nav aria-label="Footer navigation" class="flex flex-wrap gap-2 text-sm">
                <a href="{{ url('/home') }}" class="rounded-lg px-3 py-3 hover:bg-neutral-100">Home</a>
                <a href="{{ url('/home#about') }}" class="rounded-lg px-3 py-3 hover:bg-neutral-100">How it works</a>
                <a href="{{ url('/login') }}" class="rounded-lg px-3 py-3 hover:bg-neutral-100">Log in</a>
            </nav>
        </div>
    </footer>
</body>

</html>
