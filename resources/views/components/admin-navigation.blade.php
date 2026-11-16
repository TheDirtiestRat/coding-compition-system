@php
    $navigation = [
        'Competition' => [
            ['dashboard', 'Overview', 'overview'],
            ['events', 'Event details', 'calendar'],
            ['candidates', 'Competitors', 'users'],
            ['challenges', 'Challenges', 'code'],
        ],
        'Live tools' => [
            ['submissions', 'Submissions', 'submission'],
            ['countdown', 'Timer monitor', 'timer'],
            ['results', 'Results', 'trophy'],
        ],
        'Settings' => [
            ['programming-languages', 'Languages', 'code'],
        ],
    ];
@endphp

<nav aria-label="Admin navigation" class="space-y-5">
    @foreach ($navigation as $group => $links)
        <div>
            <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-widest admin-muted">{{ $group }}</p>
            <div class="space-y-1">
                @foreach ($links as [$page, $label, $icon])
                    <a href="{{ url('/admin/' . $page) }}"
                        @if (request()->is('admin/' . $page, 'admin/' . $page . '/*')) aria-current="page" @endif
                        class="admin-nav-link flex min-h-11 items-center gap-3 rounded-lg border border-transparent px-3 text-sm font-medium admin-muted hover:bg-base-200 hover:text-base-content lg:min-h-10">
                        <x-admin-icon :name="$icon" />
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach
</nav>
