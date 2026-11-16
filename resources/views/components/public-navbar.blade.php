@props(['active' => 'home'])

<header class="sticky top-0 z-50 bg-white/95 text-neutral-900">
    <nav aria-label="Main navigation" class="max-w-7xl mx-auto px-6 lg:px-12 py-4 flex flex-wrap items-center justify-between gap-4">
        <a href="{{ url('/') }}" class="flex min-w-0 items-center gap-3 rounded-lg">
            <span class="w-10 h-10 shrink-0 rounded-xl bg-black flex items-center justify-center" aria-hidden="true">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                </svg>
            </span>
            <span>
                <span class="block text-lg font-bold">Coding Competition</span>
                <span class="block text-xs text-neutral-600">ACLC Programmers' Guild</span>
            </span>
        </a>
        <div class="flex flex-wrap items-center gap-2 sm:gap-4">
            <a href="{{ $active === 'home' ? '#home' : url('/') }}" @if ($active === 'home') aria-current="page" @endif class="px-2 sm:px-3 py-3 rounded-lg text-sm font-medium hover:bg-neutral-100">Home</a>
            @if ($active === 'home')
                <a href="#about" class="px-2 sm:px-3 py-3 rounded-lg text-sm font-medium hover:bg-neutral-100">How to play</a>
            @else
                <a href="{{ url('/credits') }}" @if ($active === 'credits') aria-current="page" @endif
                    @class(['px-2 sm:px-3 py-3 rounded-lg text-sm font-medium hover:bg-neutral-100', 'bg-neutral-100' => $active === 'credits'])>Meet the team</a>
            @endif
            @if ($active !== 'login')
                <a href="{{ url('/login') }}" class="motion-button px-5 py-3 rounded-full bg-black text-white text-sm font-semibold hover:bg-neutral-800">Log in</a>
            @endif
        </div>
    </nav>
</header>
