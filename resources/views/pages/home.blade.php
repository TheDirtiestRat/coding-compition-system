<x-public-layout title="Home" active="home">
    @php
        $timer = \App\Support\CompetitionTimer::state();
        $status = match (true) {
            (bool) $timer['isTime'] => 'Ended',
            $timer['paused'] => 'Paused',
            $timer['started'] && $timer['endTime'] !== null => 'In progress',
            default => 'Not started',
        };
    @endphp

    <!-- Hero -->
    <section id="home" aria-labelledby="hero-title" class="px-6 pt-8 sm:pt-10 pb-8">
        <div class="max-w-5xl mx-auto text-center">
            <p class="motion-enter text-sm font-medium text-neutral-800 mb-5">A little curiosity goes a long way.</p>
            <h1 id="hero-title" class="motion-enter text-4xl sm:text-5xl lg:text-[64px] font-medium tracking-tight leading-tight mb-5" style="--motion-delay: 60ms">
                Code. Compete. Conquer.
            </h1>
            <p class="motion-enter max-w-2xl mx-auto text-base sm:text-lg leading-relaxed text-neutral-600" style="--motion-delay: 120ms">
                Solve coding challenges. Write your solution. Compete with your peers.
            </p>

            <div class="motion-enter flex flex-col sm:flex-row justify-center gap-3 mt-6" style="--motion-delay: 180ms">
                <a href="{{ url('login') }}" class="motion-button px-7 py-3 bg-black text-white font-semibold rounded-full hover:bg-neutral-800">
                    Log in to compete <span aria-hidden="true" class="ml-2">&rarr;</span>
                </a>
                <a href="#about" class="motion-button px-7 py-3 bg-white border border-neutral-200 font-medium rounded-full hover:bg-neutral-100">
                    How to play
                </a>
            </div>
            <p class="text-xs sm:text-sm leading-relaxed text-neutral-600 mt-3">Use your competition account to get started.</p>

            <div class="flex flex-wrap items-center justify-center gap-x-5 gap-y-2 mt-6 text-sm text-neutral-600">
                <p>Competition: <span class="font-medium text-neutral-900">{{ $status }}</span></p>
                <p class="min-w-0 wrap-anywhere">Event: <span class="font-medium text-neutral-900">{{ $event_title }}</span></p>
            </div>
            <p class="mt-2 text-xs text-neutral-500">Refresh for the latest status.</p>

            <!-- Illustrative example only; never expose a real challenge here. -->
            <div class="motion-reveal max-w-3xl mx-auto mt-6 rounded-3xl border border-neutral-200 bg-white text-left shadow-sm overflow-hidden" style="--motion-delay: 240ms">
                <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b border-neutral-100">
                    <h2 class="text-sm font-semibold">Inside a coding challenge</h2>
                    <span class="text-xs font-medium text-neutral-600 bg-neutral-100 rounded-full px-3 py-1">Example only</span>
                </div>
                <div class="grid md:grid-cols-2">
                    <div class="p-6 sm:p-8">
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-neutral-100 text-neutral-800" aria-hidden="true">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                        </span>
                        <h3 class="text-2xl font-medium tracking-tight mt-4 mb-3">Write your first solution.</h3>
                        <p class="text-sm leading-relaxed text-neutral-600">Read two numbers and print their sum. For example, inputs 7 and 5 should produce 12.</p>
                    </div>
                    <div class="m-4 mt-0 md:mt-4 rounded-2xl bg-neutral-900 p-6 sm:p-8 text-neutral-100 flex flex-col justify-center min-w-0">
                        <p class="text-xs font-mono text-neutral-400 mb-5">solution.py</p>
                        <pre class="font-mono text-sm leading-relaxed whitespace-pre-wrap wrap-anywhere"><code>a = <span class="text-violet-300">int</span>(input())
b = <span class="text-violet-300">int</span>(input())
<span class="text-violet-300">print</span>(a + b)<span class="terminal-cursor" aria-hidden="true"></span></code></pre>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-6 lg:px-12">
        <!-- How to play -->
        <section id="about" aria-labelledby="how-to-play-title" class="py-8 sm:py-10">
            <div class="motion-reveal mb-9 text-center">
                <p class="text-sm font-medium text-neutral-800 mb-3">Ready, set, solve</p>
                <h2 id="how-to-play-title" class="text-3xl sm:text-4xl font-medium tracking-tight mb-3">Three steps to compete</h2>
                <p class="text-neutral-600">From your first login to your final submission.</p>
            </div>
            <ol class="grid md:grid-cols-3 gap-5">
                <li class="motion-reveal rounded-3xl bg-neutral-100/80 p-7 sm:p-8">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white text-sm font-medium text-neutral-800" aria-hidden="true">01</span>
                    <h3 class="text-xl font-medium tracking-tight mt-6 mb-3">Log in</h3>
                    <p class="text-sm leading-relaxed text-neutral-600">Use your competition account. Need one? <a href="{{ url('/register') }}" class="rounded-sm font-medium text-neutral-900 underline underline-offset-4">Register</a> when your organizer opens sign-ups.</p>
                </li>
                <li class="motion-reveal rounded-3xl bg-neutral-100/80 p-7 sm:p-8" style="--motion-delay: 60ms">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white text-sm font-medium text-neutral-800" aria-hidden="true">02</span>
                    <h3 class="text-xl font-medium tracking-tight mt-6 mb-3">Solve challenges</h3>
                    <p class="text-sm leading-relaxed text-neutral-600">Choose a challenge, read the instructions, and write your solution. Use program input to test your code before submitting.</p>
                </li>
                <li class="motion-reveal rounded-3xl bg-neutral-100/80 p-7 sm:p-8" style="--motion-delay: 120ms">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white text-sm font-medium text-neutral-800" aria-hidden="true">03</span>
                    <h3 class="text-xl font-medium tracking-tight mt-6 mb-3">Submit your solution</h3>
                    <p class="text-sm leading-relaxed text-neutral-600">Send your solution for scoring and track your progress through the competition. Keep an eye on the timer while you work.</p>
                </li>
            </ol>
        </section>
    </div>

</x-public-layout>
