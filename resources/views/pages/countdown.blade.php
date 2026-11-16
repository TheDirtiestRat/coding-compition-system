<x-admin-display-layout title="Timer monitor">
    <div class="min-h-[70vh] flex flex-col items-center justify-center gap-5 text-center">
        <h1 class="text-lg font-semibold wrap-anywhere">@include('components.title')</h1>
        <p class="text-sm admin-muted">Time remaining</p>
        <p id="countdown" role="timer" aria-label="Time remaining" class="text-[clamp(2.25rem,12vw,12.5rem)] font-mono font-semibold tracking-tight tabular-nums leading-tight">00:00:00</p>
    </div>
    @push('scripts')
        @include('components.countdown-script')
    @endpush
</x-admin-display-layout>
