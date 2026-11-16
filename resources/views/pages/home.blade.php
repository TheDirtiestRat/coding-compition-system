<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@include('components.title')</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @endif

    <style>
        a:focus-visible {
            outline: 3px solid #171717;
            outline-offset: 4px;
        }

        section[id],
        main[id] {
            scroll-margin-top: 11rem;
        }

        @media (min-width: 640px) {
            section[id],
            main[id] {
                scroll-margin-top: 7rem;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-white text-neutral-900 font-sans">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:block focus:bg-white focus:p-4">Skip to content</a>

    <header class="sticky top-0 z-20 border-b border-neutral-200 bg-white">
        <nav aria-label="Main navigation" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 py-4 flex flex-wrap items-center justify-between gap-4">
            <a href="#home" class="flex min-w-0 items-center gap-3 rounded-lg">
                <span class="w-9 h-9 shrink-0 rounded-xl bg-black flex items-center justify-center" aria-hidden="true">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                </span>
                <span class="text-base font-semibold">Coding competition</span>
            </a>
            <div class="flex flex-wrap items-center gap-2 sm:gap-4">
                <a href="#home" class="rounded-lg px-3 py-3 text-sm font-medium hover:bg-neutral-100">Home</a>
                <a href="#about" class="rounded-lg px-3 py-3 text-sm font-medium hover:bg-neutral-100">How it works</a>
                <a href="{{ url('/login') }}" class="rounded-full bg-black px-5 py-3 text-sm font-semibold text-white hover:bg-neutral-800">Log in</a>
            </div>
        </nav>
    </header>

    <main id="main-content" tabindex="-1">
        <section id="home" aria-labelledby="hero-title" class="px-4 sm:px-6 pt-10 sm:pt-14 pb-10">
            <div class="max-w-5xl mx-auto text-center">
                <p class="mb-5 text-sm font-medium text-neutral-600 wrap-anywhere">@include('components.title')</p>
                <h1 id="hero-title" class="text-4xl sm:text-5xl lg:text-[64px] font-medium tracking-tight leading-tight">
                    Code. Compete. Conquer.
                </h1>
                <p class="max-w-2xl mx-auto mt-5 text-base sm:text-lg leading-relaxed text-neutral-600">
                    Solve coding challenges, test your code, and submit your best solution.
                </p>

                <div class="mt-7 flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-3">
                    <a href="{{ url('/login') }}" class="rounded-full bg-black px-7 py-3 font-semibold text-white hover:bg-neutral-800">
                        Log in to compete <span aria-hidden="true" class="ml-2">&rarr;</span>
                    </a>
                    <a href="#about" class="rounded-full border border-neutral-300 bg-white px-7 py-3 font-medium hover:bg-neutral-100">
                        How it works
                    </a>
                </div>

                <div class="max-w-3xl mx-auto mt-10 overflow-hidden rounded-3xl border border-neutral-200 bg-white text-left shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-neutral-200 px-6 py-4">
                        <h2 class="text-sm font-semibold">Inside a coding challenge</h2>
                        <span class="rounded-full bg-neutral-100 px-3 py-1 text-xs font-medium text-neutral-600">Example only</span>
                    </div>
                    <div class="grid md:grid-cols-2">
                        <div class="p-6 sm:p-8">
                            <h3 class="text-2xl font-medium tracking-tight">Add two numbers.</h3>
                            <p class="mt-3 text-sm leading-relaxed text-neutral-600">Read two numbers and print their sum.</p>
                            <dl class="mt-5 flex gap-10 text-sm">
                                <div>
                                    <dt class="mb-2 text-neutral-600">Input</dt>
                                    <dd class="font-mono whitespace-pre-line">7
5</dd>
                                </div>
                                <div>
                                    <dt class="mb-2 text-neutral-600">Output</dt>
                                    <dd class="font-mono">12</dd>
                                </div>
                            </dl>
                        </div>
                        <div class="m-4 mt-0 md:mt-4 flex min-w-0 flex-col justify-center rounded-2xl bg-neutral-900 p-6 sm:p-8 text-neutral-100">
                            <p class="mb-5 font-mono text-xs text-neutral-400">solution.py</p>
                            <pre class="font-mono text-sm leading-relaxed whitespace-pre-wrap wrap-anywhere"><code>a = <span class="text-violet-300">int</span>(input())
b = <span class="text-violet-300">int</span>(input())
<span class="text-violet-300">print</span>(a + b)</code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12">
            <section id="about" aria-labelledby="steps-title" class="py-10 sm:py-14">
                <div class="mb-8 text-center">
                    <h2 id="steps-title" class="text-3xl sm:text-4xl font-medium tracking-tight">Three steps to compete</h2>
                    <p class="mt-3 text-neutral-600">From your first login to your final submission.</p>
                </div>

                <ol class="grid md:grid-cols-3 gap-5">
                    <li class="rounded-3xl bg-neutral-100 p-6 sm:p-8">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white font-mono text-sm" aria-hidden="true">01</span>
                        <h3 class="mt-6 text-xl font-medium tracking-tight">Log in</h3>
                        <p class="mt-3 text-sm leading-relaxed text-neutral-600">
                            Use your competition account. Need one?
                            <a href="{{ url('/register') }}" class="rounded-sm font-medium text-neutral-900 underline underline-offset-4">Register</a>
                            when your organizer opens sign-ups.
                        </p>
                    </li>
                    <li class="rounded-3xl bg-neutral-100 p-6 sm:p-8">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white font-mono text-sm" aria-hidden="true">02</span>
                        <h3 class="mt-6 text-xl font-medium tracking-tight">Solve a problem</h3>
                        <p class="mt-3 text-sm leading-relaxed text-neutral-600">Choose a challenge, read the instructions, and write your solution. Use program input to test your code before submitting.</p>
                    </li>
                    <li class="rounded-3xl bg-neutral-100 p-6 sm:p-8">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white font-mono text-sm" aria-hidden="true">03</span>
                        <h3 class="mt-6 text-xl font-medium tracking-tight">Submit your solution</h3>
                        <p class="mt-3 text-sm leading-relaxed text-neutral-600">Send your solution for scoring and track your progress through the competition. Keep an eye on the timer while you work.</p>
                    </li>
                </ol>
            </section>

            <section aria-labelledby="team-title" class="my-6 sm:my-10 rounded-3xl border border-neutral-200 px-6 py-8 sm:p-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="max-w-2xl">
                    <p class="mb-3 text-sm text-neutral-600">ACLC Programmers' Guild</p>
                    <h2 id="team-title" class="text-2xl sm:text-3xl font-medium tracking-tight">Meet the team behind the competition.</h2>
                    <p class="mt-3 text-sm leading-relaxed text-neutral-600">Get to know the guild members who built the platform.</p>
                </div>
                <a href="{{ url('/credits') }}" class="shrink-0 self-start rounded-full border border-neutral-300 px-6 py-3 text-sm font-semibold hover:bg-neutral-100 md:self-center">
                    Meet the team <span aria-hidden="true" class="ml-2">&rarr;</span>
                </a>
            </section>
        </div>
    </main>

    <footer class="mt-12 border-t border-neutral-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 py-8 flex flex-wrap items-center justify-between gap-5">
            <div>
                <p class="font-semibold">Coding competition</p>
                <p class="mt-2 text-sm text-neutral-600">ACLC Programmers' Guild &middot; &copy; {{ date('Y') }}</p>
            </div>
            <nav aria-label="Footer navigation" class="flex flex-wrap gap-2 text-sm">
                <a href="#home" class="rounded-lg px-3 py-3 hover:bg-neutral-100">Home</a>
                <a href="#about" class="rounded-lg px-3 py-3 hover:bg-neutral-100">How it works</a>
                <a href="{{ url('/login') }}" class="rounded-lg px-3 py-3 hover:bg-neutral-100">Log in</a>
            </nav>
        </div>
    </footer>
</body>

</html>
