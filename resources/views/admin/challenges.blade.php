@extends('layouts.admin')

@section('admin-content')
    <div class="flex flex-row gap-3 w-full">
        {{-- forms --}}
        <div class="basis-full bg-base-100 rounded-xl p-3">
            <form method="POST" action="{{ url('/admin/add-candidate') }}">
                @csrf
                {{-- form --}}
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Challenge Title</legend>
                    <input type="text" class="input w-full" name="title" placeholder="Type here" />
                    <legend class="fieldset-legend">Description</legend>
                    <textarea class="textarea h-[200px] w-full" name="description" placeholder="What is the Challenge about?"></textarea>
                    <p class="label">Optional</p>

                    <div class="flex flex-row gap-3">
                        <fieldset class="fieldset w-full">
                            <legend class="fieldset-legend">Equevalent Points</legend>
                            <input type="number" class="input w-full" name="points" placeholder="Type here" />
                        </fieldset>
                        <fieldset class="fieldset basis-full">
                            <legend class="fieldset-legend">Difficulty</legend>
                            <select class="select">
                                <option value="easy">Easy</option>
                                <option value="medium">Medium</option>
                                <option value="hard">Hard</option>
                                <option value="challenge">Challenge</option>
                            </select>
                        </fieldset>
                    </div>

                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend">Answer Key file</legend>
                        <input type="file" class="file-input w-full file-input-ghost"/>
                        <label class="label">Max size 2MB</label>
                    </fieldset>
                    <div class="divider"></div>
                    <button class="btn btn-soft">Add Candidate</button>
                </fieldset>
            </form>
        </div>
        {{-- challenge list --}}
        <div class="basis-full bg-base-100 rounded-xl p-3">

        </div>
    </div>
@endsection
