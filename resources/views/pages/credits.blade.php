<x-public-layout title="Guild credits" active="credits">
    <div class="max-w-7xl mx-auto px-6 lg:px-12 pt-10 pb-12 sm:pt-14">
        <div class="motion-enter max-w-2xl mb-8 sm:mb-10">
            <p class="text-sm text-neutral-600 mb-3">ACLC Programmers' Guild</p>
            <h1 class="text-4xl sm:text-5xl font-medium tracking-tight">Meet the guild.</h1>
            <p class="mt-4 text-base leading-relaxed text-neutral-600">The people behind our school's coding competition platform.</p>
        </div>

        @include('components.guild-members')
    </div>
</x-public-layout>
