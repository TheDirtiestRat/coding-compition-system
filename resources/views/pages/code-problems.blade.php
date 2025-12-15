@extends('layouts.code-editor')

@section('code-content')
    <div class="grid grid-cols-2 md:grid-cols-4 w-full gap-3">
        {{-- divider --}}
        <div class="divider col-span-full">Easy Problems</div>
        @for ($i = 1; $i <= 7; $i++)
            <div class="card bg-base-300 rounded-box h-auto shadow border border-gray-500 p-2 flex flex-col gap-1">
                Problem {{ $i }}
                <br>
                <span class="text-2xl font-semibold basis-full">Problem Title Problem Problem Problem Problem Title Problem
                    Title</span>
                <span>Points: 200</span>
                <div class="flex flex-row justify-between">
                    <a href="">
                        <button class="btn">
                            Solve it
                        </button>
                    </a>
                    <button class="btn btn-ghost" onclick="my_modal_2.showModal()">
                        Details
                    </button>
                </div>
            </div>
        @endfor

        {{-- solved indicator problem --}}
        <div class="card bg-success rounded-box h-[180px] shadow p-2 flex flex-col gap-1">
            Problem {{ $i }}
            <br>
            <span class="text-2xl font-semibold basis-full">Problem Title Problem Title Problem Title</span>
            <div class="flex flex-row justify-between">
                <button class="btn btn-ghost">
                    Problem Solved
                </button>
                <button class="btn btn-ghost" onclick="my_modal_2.showModal()">
                    Details
                </button>
            </div>
        </div>
    </div>

    {{-- modal --}}
    <dialog id="my_modal_2" class="modal">
        <div class="modal-box w-[800px]">
            <h3 class="text-lg font-bold">Problem #99</h3>
            <p class="py-4">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quod neque placeat error natus maxime temporibus possimus voluptatem ea consectetur aut sapiente fugit illum ex totam porro eligendi dolor, provident odio.</p>
            <p class="py-4">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quod neque placeat error natus maxime temporibus possimus voluptatem ea consectetur aut sapiente fugit illum ex totam porro eligendi dolor, provident odio.</p>
            <p class="py-4">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quod neque placeat error natus maxime temporibus possimus voluptatem ea consectetur aut sapiente fugit illum ex totam porro eligendi dolor, provident odio.</p>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
@endsection
