@extends('layouts.code-editor')

@section('code-content')
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 place-content-center gap-3">
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
            <div id="problem_photo_container" class="hidden mt-2 flex flex-wrap gap-2">
            </div>
            <p class="py-3" id="problem_description">Loading...</p>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
@endsection
