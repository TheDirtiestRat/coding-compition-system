@php
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
@endphp

<section aria-label="Guild members" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
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

        <article class="flex flex-col rounded-2xl border border-neutral-200 bg-white p-6">
            <div class="flex items-start gap-4">
                @if (!empty($member['photo']) && file_exists(public_path($member['photo'])))
                    <img src="{{ asset($member['photo']) }}" alt="{{ $member['name'] }}" width="80" height="80" loading="lazy"
                        class="h-20 w-20 shrink-0 rounded-xl object-cover border border-neutral-200">
                @else
                    <div aria-hidden="true" class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl bg-neutral-100 text-xl font-semibold text-neutral-900">
                        {{ $initials }}
                    </div>
                @endif

                <div class="min-w-0">
                    <p class="text-xs font-medium text-neutral-600 mb-2">{{ $member['role'] }}</p>
                    <h2 class="text-lg font-medium tracking-tight leading-snug">{{ $member['name'] }}</h2>
                    <p class="mt-1 text-xs text-neutral-600">Age {{ $member['age'] }}</p>
                </div>
            </div>

            @if (!empty($member['github']))
                <div class="mt-auto pt-5">
                    <a href="{{ $member['github'] }}" target="_blank" rel="noopener noreferrer"
                        aria-label="View {{ $member['name'] }} on GitHub (opens in a new tab)"
                        class="flex min-h-11 items-center justify-between gap-3 rounded-lg border-t border-neutral-100 px-2 py-3 text-sm font-medium text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900">
                        View GitHub
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 17 17 7M7 7h10v10" />
                        </svg>
                    </a>
                </div>
            @endif
        </article>
    @endforeach
</section>
