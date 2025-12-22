@extends('layouts.code-editor')

@section('code-content')
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 w-full gap-3">
        {{-- divider --}}
        <div class="divider col-span-full">Easy Problems</div>
        @foreach ($easy_challenges as $challenge)
            <div
                class="card @if ($challenge->is_solved) bg-success @else bg-base-300 @endif rounded-box h-auto shadow p-2 flex flex-col gap-1">
                Problem {{ $challenge->id }}
                <br>
                <span class="text-2xl font-semibold basis-full">{{ $challenge->title }}</span>
                <span>Points: {{ $challenge->points }}</span>
                <div class="flex flex-row justify-between">
                    @if (!$challenge->is_solved)
                        <a href="{{ url('/code-editor/' . $challenge->id) }}">
                            <button class="btn">
                                Solve it
                            </button>
                        </a>
                    @else
                        <button class="btn btn-ghost">
                            Problem Solved
                        </button>
                    @endif
                    <button class="btn btn-ghost" id="challenge_details_btn" value="{{ $challenge->id }}">
                        Details
                    </button>
                </div>
            </div>
        @endforeach
        <div class="divider col-span-full">Medium Problems</div>
        @foreach ($medium_challenges as $challenge)
            <div class="card bg-base-300 rounded-box h-auto shadow p-2 flex flex-col gap-1">
                Problem {{ $challenge->id }}
                <br>
                <span class="text-2xl font-semibold basis-full">{{ $challenge->title }}</span>
                <span>Points: {{ $challenge->points }}</span>
                <div class="flex flex-row justify-between">
                    <a href="{{ url('/code-editor/' . $challenge->id) }}">
                        <button class="btn">
                            Solve it
                        </button>
                    </a>
                    <button class="btn btn-ghost" id="challenge_details_btn" value="{{ $challenge->id }}">
                        Details
                    </button>
                </div>
            </div>
        @endforeach
        <div class="divider col-span-full">Hard Problems</div>
        @foreach ($hard_challenges as $challenge)
            <div class="card bg-base-300 rounded-box h-auto shadow p-2 flex flex-col gap-1">
                Problem {{ $challenge->id }}
                <br>
                <span class="text-2xl font-semibold basis-full">{{ $challenge->title }}</span>
                <span>Points: {{ $challenge->points }}</span>
                <div class="flex flex-row justify-between">
                    <a href="{{ url('/code-editor/' . $challenge->id) }}">
                        <button class="btn">
                            Solve it
                        </button>
                    </a>
                    <button class="btn btn-ghost" id="challenge_details_btn" value="{{ $challenge->id }}">
                        Details
                    </button>
                </div>
            </div>
        @endforeach
        <div class="divider col-span-full">Challenge Problems</div>
        @foreach ($challenges as $challenge)
            <div class="card bg-base-300 rounded-box h-auto shadow p-2 flex flex-col gap-1">
                Problem {{ $challenge->id }}
                <br>
                <span class="text-2xl font-semibold basis-full">{{ $challenge->title }}</span>
                <span>Points: {{ $challenge->points }}</span>
                <div class="flex flex-row justify-between">
                    <a href="{{ url('/code-editor/' . $challenge->id) }}">
                        <button class="btn">
                            Solve it
                        </button>
                    </a>
                    <button class="btn btn-ghost" id="challenge_details_btn" value="{{ $challenge->id }}">
                        Details
                    </button>
                </div>
            </div>
        @endforeach

        {{-- solved indicator problem --}}
        {{-- <div class="card bg-success rounded-box h-[180px] shadow p-2 flex flex-col gap-1">
            Problem {{ 23 }}
            <br>
            <span class="text-2xl font-semibold basis-full">Problem Title Problem Title Problem Title</span>
            <div class="flex flex-row justify-between">
                <button class="btn btn-ghost">
                    Problem Solved
                </button>
                <button class="btn btn-ghost" id="challenge_details_btn" value="{{ $challenge->id }}">
                    Details
                </button>
            </div>
        </div> --}}
    </div>

    {{-- modal --}}
    <dialog id="my_modal_2" class="modal">
        <div class="modal-box w-[800px]">
            <h3 class="text-lg font-bold">Problem <span id="problem_id">99</span></h3>
            <p class="py-3" id="problem_description">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quod neque
                placeat error natus
                maxime temporibus possimus voluptatem ea consectetur aut sapiente fugit illum ex totam porro eligendi dolor,
                provident odio.</p>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
@endsection
