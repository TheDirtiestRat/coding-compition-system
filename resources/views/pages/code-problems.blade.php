@extends('layouts.code-editor')

@section('code-content')
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 w-full gap-3">
        <div class="divider col-span-full">Easy Problems</div>
        @foreach ($easy_challenges as $challenge)
            <x-challenge-card :challenge="$challenge" />
        @endforeach

        <div class="divider col-span-full">Medium Problems</div>
        @foreach ($medium_challenges as $challenge)
            <x-challenge-card :challenge="$challenge" />
        @endforeach

        <div class="divider col-span-full">Hard Problems</div>
        @foreach ($hard_challenges as $challenge)
            <x-challenge-card :challenge="$challenge" />
        @endforeach

        <div class="divider col-span-full">Challenge Problems</div>
        @foreach ($challenges as $challenge)
            <x-challenge-card :challenge="$challenge" />
        @endforeach
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
