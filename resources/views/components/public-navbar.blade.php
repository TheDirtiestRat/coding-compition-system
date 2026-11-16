@props(['active' => 'home'])

<header class="sticky top-0 z-20 border-b border-neutral-200 bg-white">
    <nav aria-label="Main navigation" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 py-4 flex flex-wrap items-center justify-between gap-4">
        <a href="{{ url('/home') }}" class="flex min-w-0 items-center gap-3 rounded-lg">
            <span class="w-9 h-9 shrink-0 rounded-xl bg-black flex items-center justify-center" aria-hidden="true">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                </svg>
            </span>
            <span class="text-base font-semibold">Coding competition</span>
        </a>
        <div class="flex flex-wrap items-center gap-2 sm:gap-4">
            <a href="{{ url('/home') }}" @if ($active === 'home') aria-current="page" @endif
                @class(['rounded-lg px-3 py-3 text-sm font-medium hover:bg-neutral-100', 'bg-neutral-100' => $active === 'home'])>Home</a>
            <a href="{{ url('/credits') }}" @if ($active === 'credits') aria-current="page" @endif
                @class(['rounded-lg px-3 py-3 text-sm font-medium hover:bg-neutral-100', 'bg-neutral-100' => $active === 'credits'])>Credits</a>
            <a href="{{ url('/login') }}" class="rounded-full bg-black px-5 py-3 text-sm font-semibold text-white hover:bg-neutral-800">Log in</a>
        </div>
    </nav>
</header>
