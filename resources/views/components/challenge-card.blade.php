@props(['challenge'])

@php
    $statusStyles = match ($challenge->status) {
        'reviewed' => [
            'card' => 'border border-success bg-success/5',
            'badge' => 'badge-success',
            'badgeLabel' => 'Reviewed',
            'button' => 'btn-success btn-disabled',
            'label' => 'Reviewed',
            'clickable' => false,
        ],
        'submitted' => [
            'card' => 'border border-warning bg-warning/5',
            'badge' => 'badge-warning',
            'badgeLabel' => 'Submitted',
            'button' => 'btn-warning',
            'label' => 'Submitted',
            'clickable' => true,
        ],
        default => [
            'card' => 'border border-base-300 hover:border-primary hover:bg-base-200 transition',
            'badge' => 'badge',
            'badgeLabel' => 'Unsolved',
            'button' => 'btn-primary',
            'label' => 'Solve Challenge',
            'clickable' => true,
        ],
    };
@endphp

<div class=" {{ $statusStyles['card'] }} rounded-2xl h-auto shadow p-4 flex flex-col gap-2">
    {{-- Header --}}
    <div class="flex justify-between items-start">
        <div>
            <span class="text-sm opacity-60">Problem {{ $challenge->id }}</span>
            <h2 class="text-2xl font-semibold">{{ $challenge->title }}</h2>
        </div>

        <span class="badge {{ $statusStyles['badge'] }}">
            {{ $statusStyles['badgeLabel'] }}
        </span>
    </div>

    {{-- Score --}}
    <span class="text-sm mt-1">
        Score:
        <span class="font-medium">
            {{ $challenge->score ?? 0 }} / {{ $challenge->points }}
        </span>
    </span>

    {{-- Actions --}}
    <div class="flex justify-between items-center mt-3">
        @if ($statusStyles['clickable'])
            <a href="{{ url('/code-editor/' . $challenge->id) }}">
                <button class="btn {{ $statusStyles['button'] }}">
                    {{ $statusStyles['label'] }}
                </button>
            </a>
        @else
            <button class="btn {{ $statusStyles['button'] }} cursor-not-allowed opacity-70">
                {{ $statusStyles['label'] }}
            </button>
        @endif

        <button class="btn btn-ghost btn-sm" id="challenge_details_btn" value="{{ $challenge->id }}">
            Details
        </button>
    </div>
</div>
