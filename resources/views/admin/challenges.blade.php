@extends('layouts.admin')

@section('admin-content')
    {{-- alert --}}
    @if (session('success') || session('error') || $errors->any())
        <div id="alert_container" class="w-full flex-col gap-1 flex mb-3">
            @if (session('success'))
                <div role="alert" id="alert_success" class="alert alert-success w-full flex">
                    <span id="success_alert_text">{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div role="alert" id="alert_error" class="alert alert-error w-full flex">
                    <span id="success_alert_text">{{ session('error') }}</span>
                </div>
            @endif
            @if ($errors->any())
                <div role="alert" id="alert_error" class="alert alert-error w-full flex">
                    <ul class="p-2 px-3 rounded-b-xl">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif
    <div class="flex flex-row gap-3 w-full">
        {{-- forms --}}
        <div class="basis-[90%] bg-base-100 rounded-xl p-3">
            <form method="POST" action="{{ url('/admin/create-challenge') }}" id="challenge_form"
                enctype="multipart/form-data">
                @csrf
                {{-- form --}}
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Challenge ID</legend>
                    <input type="text" class="input" name="challenge_id" id="challenge_id"
                        placeholder="Challenge Id" readonly />
                    <legend class="fieldset-legend">Challenge Title</legend>
                    <input type="text" class="input w-full" name="title" id="title" placeholder="Type here" />
                    <legend class="fieldset-legend">Description</legend>
                    <textarea class="textarea h-[200px] w-full" name="description" id="description"
                        placeholder="What is the Challenge about?"></textarea>
                    <p class="label">Optional</p>

                    <div class="flex flex-row gap-3">
                        <fieldset class="fieldset w-full">
                            <legend class="fieldset-legend">Equivalent Points</legend>
                            <input type="number" class="input w-full" name="points" id="points"
                                placeholder="Type here" />
                        </fieldset>
                        <fieldset class="fieldset basis-full">
                            <legend class="fieldset-legend">Difficulty</legend>
                            <select class="select" id="difficulty" name="difficulty">
                                <option value="easy">Easy</option>
                                <option value="medium">Medium</option>
                                <option value="hard">Hard</option>
                                <option value="challenge">Challenge</option>
                            </select>
                        </fieldset>
                    </div>

                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend">Answer Key File</legend>
                        <input type="file" class="file-input w-full file-input-ghost" name="file_answer_key" />
                        <label class="label">Max size 2MB</label>
                    </fieldset>
                    <div class="divider"></div>
                    <button type="submit" id="challenge_submit_btn" class="btn btn-primary">Add Challenge</button>
                </fieldset>
            </form>
        </div>
        {{-- challenge list --}}
        <div class="basis-full bg-base-100 rounded-xl overflow-hidden p-3">
            <table class="table table-zebra">
                <!-- head -->
                <thead class="bg-slate-700/50">
                    <tr class="text-center text-neutral-200">
                        <th>ID</th>
                        <th>Title</th>
                        <th>Difficulty</th>
                        <th>Points</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($challenges as $challenge)
                        <tr class="text-center">
                            <th>{{ $challenge->id }}</th>
                            <td>{{ $challenge->title }}</td>
                            <td>{{ $challenge->difficulty }}</td>
                            <td>{{ $challenge->points }}</td>
                            <td class="flex flex-row items-center justify-center gap-2">
                                <button class="btn btn-xs" onclick="edit_challenge({{ $challenge->id }})">Edit</button>
                                <form action="{{ url('/admin/delete-challenge/' . $challenge->id) }}"
                                    class="challenge_delete_form" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-error">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('admin-scripts')
    <script>
        function edit_challenge(challenge_id) {
            const form = document.getElementById('challenge_form')
            const challenge_id_elem = document.getElementById("challenge_id");
            const challenge_title = document.getElementById("title");
            const challenge_description = document.getElementById("description");
            const challenge_difficulty = document.getElementById("difficulty");
            const challenge_points = document.getElementById("points");
            const submit_btn = document.getElementById("challenge_submit_btn");

            challenge_id_elem.value = challenge_id

            let challenge_data = {}

            fetch('/admin/api/get-challenge-details/' + challenge_id) // Assumes you have this API route set up in Laravel
                .then(response => response.json())
                .then(data => {
                    challenge_data = data
                    console.log('Dynamic data:', data);
                    // Update the DOM with the fetched data
                    // console.log(challenge_data)
                    challenge_title.value = data[0]['title']
                    challenge_description.value = data[0]['description']
                    challenge_difficulty.value = data[0]['difficulty']
                    challenge_points.value = data[0]['points']
                });

            if (form) {
                submit_btn.innerText = "Update Details"
                form.action = "{{ url('/admin/update-challenge') }}"
                console.log(form.action)
            }
        }
    </script>
@endsection
