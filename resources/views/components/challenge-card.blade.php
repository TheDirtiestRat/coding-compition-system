@props(['challenge'])

@php
    $canSolve = $competition_started ?? true;

    $statusStyles = match (true) {
        !$canSolve => [
            'card' => 'border border-base-300 opacity-60',
            'badge' => 'badge',
            'badgeLabel' => 'Locked',
            'button' => 'btn-neutral',
            'label' => 'Not Available',
            'clickable' => false,
        ],
        $challenge->status === 'reviewed' => [
            'card' => 'border border-success bg-success/5',
            'badge' => 'badge-success',
            'badgeLabel' => 'Reviewed',
            'button' => 'btn-success',
            'label' => 'Reviewed',
            'clickable' => true,
        ],
        $challenge->status === 'submitted' => [
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

    @if ($challenge->relationLoaded('photos') && $challenge->photos->isNotEmpty())
        <div class="flex gap-1 overflow-x-auto">
            @foreach ($challenge->photos->take(3) as $photo)
                <img src="{{ $photo->url ?? asset('storage/' . $photo->photo_path) }}" alt="Challenge photo" class="h-20 w-20 object-cover rounded-xl flex-shrink-0" />
            @endforeach
            @if ($challenge->photos->count() > 3)
                <span class="text-sm text-gray-400 self-center">+{{ $challenge->photos->count() - 3 }} more</span>
            @endif
        </div>
    @elseif ($challenge->photo ?? false)
        <img src="{{ asset('storage/' . $challenge->photo) }}" alt="Challenge photo" class="h-32 w-full object-cover rounded-xl" />
    @endif

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

        <button class="btn btn-ghost btn-sm challenge_details_btn" value="{{ $challenge->id }}">
            Details
        </button>
    </div>
</div>
