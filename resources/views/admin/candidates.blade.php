@extends('layouts.admin')

@section('admin-title', 'Competitors')
@section('admin-description', 'Manage competitor accounts and review their solved problems.')

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

    <div class="grid min-w-0 items-start gap-6 xl:grid-cols-[340px_minmax(0,1fr)]">
        <div class="tabs tabs-lift min-w-0">
            <input type="radio" name="my_tabs_3" class="tab font-medium" aria-label="Add Candidate" checked="checked" />
            <div class="tab-content bg-base-100 border-base-300 p-5">
                {{-- for adding new candidates --}}
                <h2 class="text-lg font-semibold">Add new Candidate</h2>
                <form method="POST" action="{{ url('/admin/add-candidate') }}">
                    @csrf
                    {{-- form --}}
                    <fieldset class="fieldset mt-2">
                        <label class="fieldset-legend" for="add_candidates_event_id">Event</label>
                        <select id="add_candidates_event_id" class="select" name="event_id">
                            @forelse  ($events as $event)
                                @if ($event->isActive)
                                    <option value="{{ $event->id }}" selected>{{ $event->title }}</option>
                                @else
                                    <option value="{{ $event->id }}">{{ $event->title }}</option>
                                @endif
                            @empty
                                <option disabled selected>Choose an event</option>
                            @endforelse
                        </select>

                        <label class="fieldset-legend" for="add_candidates_candidate_name">Full name</label>
                        <input id="add_candidates_candidate_name" type="text" class="input" name="candidate_name" placeholder="Type here" />
                        {{-- <p class="label">Optional</p> --}}
                        <label class="fieldset-legend" for="add_candidates_username">Username</label>
                        <input id="add_candidates_username" type="text" class="input" name="username" placeholder="Type here" />
                        <p class="label">Optional</p>
                        <label class="fieldset-legend" for="add_candidates_user_password">Password</label>
                        <input id="add_candidates_user_password" type="text" class="input" name="user_password" placeholder="Type here" />
                        <div class="divider"></div>
                        <button class="btn btn-primary">Add Candidate</button>
                    </fieldset>
                </form>
            </div>

            <input type="radio" name="my_tabs_3" id="edit_tabs" class="tab font-medium" aria-label="Edit Candidate" />
            <div class="tab-content bg-base-100 border-base-300 p-5">
                {{-- for editing candidates details --}}
                <h2 class="text-lg font-semibold">Edit Candidate</h2>
                <form method="POST" action="{{ url('/admin/update-candidate') }}">
                    @csrf
                    {{-- form --}}
                    <fieldset class="fieldset mt-2">
                        <label class="fieldset-legend" for="candidate_id">Candidate ID: </label>
                        <input type="text" class="input" name="candidate_id" id="candidate_id" placeholder="User Id"
                            readonly />

                        <label class="fieldset-legend" for="event_id">Event</label>
                        <select class="select" name="event_id" id="event_id">
                            @forelse  ($events as $event)
                                @if ($event->isActive)
                                    <option value="{{ $event->id }}" selected>{{ $event->title }}</option>
                                @else
                                    <option value="{{ $event->id }}">{{ $event->title }}</option>
                                @endif
                            @empty
                                <option disabled selected>Choose an event</option>
                            @endforelse
                        </select>

                        <label class="fieldset-legend" for="edit_name">Full name</label>
                        <input type="text" class="input" name="edit_name" id="edit_name" placeholder="Type here" />
                        {{-- <p class="label">Optional</p> --}}
                        <label class="fieldset-legend" for="edit_username">Username</label>
                        <input type="text" class="input" name="edit_username" id="edit_username"
                            placeholder="Type here" />
                        <p class="label">Optional</p>
                        <label class="fieldset-legend" for="edit_user_password">New password</label>
                        <input type="text" class="input" name="edit_user_password" id="edit_user_password"
                            placeholder="Type here" />
                        <div class="divider"></div>
                        <button class="btn btn-primary">Update Details</button>
                    </fieldset>
                </form>
            </div>
        </div>
        {{-- <div class="basis-auto admin-card p-5 sm:p-6">


        </div> --}}
        <div class="basis-full admin-card p-5 sm:p-6">
            {{-- list of candidates --}}
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <!-- head -->
                    <thead class=" admin-muted text-center">
                        <tr>
                            <th class="bg-base-200 rounded-tl-xl">ID</th>
                            <th class="bg-base-200">Name</th>
                            <th class="bg-base-200">Username</th>
                            <th class="bg-base-200">Group</th>
                            <th class="bg-base-200">Event</th>
                            <th class="bg-base-200 rounded-tr-xl">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($candidates as $tableCandidate)
                            <tr class="text-center">
                                <th>{{ $tableCandidate->id }}</th>
                                <td>{{ $tableCandidate->fullname }}</td>
                                <td>{{ optional($tableCandidate->user)->name }}</td>
                                <td>
                                    <p class="badge badge-primary badge-soft">
                                        {{ $tableCandidate->group }}
                                    </p>
                                </td>
                                <td>{{ $tableCandidate->title }}</td>
                                <td class="flex flex-row items-center justify-center gap-2">
                                    <a href="{{ url('/admin/candidates/view/' . $tableCandidate->id) }}"
                                        class="btn btn-xs btn-info">View</a>
                                    <button class="btn btn-xs"
                                        data-id="{{ $tableCandidate->id }}" data-event-id="{{ $tableCandidate->event_id }}"
                                        data-name="{{ $tableCandidate->fullname }}" data-username="{{ optional($tableCandidate->user)->name }}"
                                        onclick="edit_candidate(this.dataset.id, this.dataset.eventId, this.dataset.name, this.dataset.username)">Edit</button>
                                    <form action="{{ url('/admin/delete-candidate/' . $tableCandidate->id) }}"
                                        class="candidate_delete_form" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-error">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="admin-muted">No competitors yet. Add an account to get started.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- candidate info --}}
    @isset($solved_challenges)
        <div class="grid min-w-0 gap-6 xl:grid-cols-[280px_minmax(0,1fr)] admin-card p-5 sm:p-6 mt-6">
            <div class="min-w-0 p-2 flex flex-col gap-2 wrap-anywhere">
                <span class="text-lg font-semibold wrap-anywhere">Candidate Details</span>
                <strong><span>Name: {{ $candidate->fullname }}</span></strong>
                <span>Username: {{ optional($candidate->user)->name }}</span>
                <span>Email: {{ optional($candidate->user)->email }}</span>
                <span>Group: {{ $candidate->group }}</span>
            </div>
            <div class="min-w-0 p-2">
                <h2 class="text-lg font-semibold">Solved Problems ({{ count($solved_challenges) }})</h2>
                {{-- list --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 2xl:grid-cols-3 gap-3 mt-3">
                    @foreach ($solved_challenges as $challenges)
                        <button type="button" class="admin-submission h-fit"
                            onclick="show_code_solutions({{ $challenges->id }})">
                            <span class="flex flex-row flex-wrap gap-2 justify-between items-center mb-1">
                                <span class="badge badge-xs badge-primary">{{ $challenges->difficulty }}</span>
                                <span class="badge badge-xs badge-primary">Points: {{ $challenges->score }} /
                                    {{ $challenges->points }}</span>
                            </span>
                            <span class="mt-2 block font-medium">{{ $challenges->title }}</span>
                            <span class="mt-2 block text-xs admin-muted">Date: {{ $challenges->created_at }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endisset

    {{-- modal --}}
    <dialog id="code_modal" class="modal" aria-labelledby="code-modal-title">
        <div class="modal-box w-11/12 max-w-5xl">
            <h3 id="code-modal-title" class="text-lg font-semibold">Code output</h3>
            <p class="basis-full bg-base-300 p-3 rounded-xl w-full whitespace-pre-wrap break-all mb-2"
                id="code_instruction">
                Instructions Code sits here...
            </p>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 h-full">
                <p class="basis-full bg-base-300 p-3 rounded-xl w-full whitespace-pre-wrap break-all font-mono"
                    id="candidate_code">
                    Candidates Code sits here...
                </p>
                <p class="basis-full p-3 rounded-xl w-full whitespace-pre-wrap break-all font-mono" id="solution_code">
                    Solution Code sits here...
                </p>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
@endsection

@section('admin-scripts')
    <script>
        function edit_candidate(user_id, event_id, name, username) {
            const edit_tab = document.getElementById("edit_tabs");
            const can_event_id = document.getElementById("event_id");
            const can_user_id = document.getElementById("candidate_id");
            const can_name = document.getElementById("edit_name");
            const can_username = document.getElementById("edit_username");
            const can_password = document.getElementById("edit_user_password");

            can_event_id.value = event_id
            can_user_id.value = user_id
            can_name.value = name;
            can_username.value = username;
            can_password.value = '';

            edit_tab.checked = true;
        }

        function show_code_solutions(no_id = 0) {
            const modal = document.getElementById("code_modal");
            // const problem_no = document.getElementById("problem_id");
            const code_info = document.getElementById("code_instruction");
            const code_input = document.getElementById("candidate_code");
            const code_output = document.getElementById("solution_code");

            fetch("/admin/get-candidates-codes/" + no_id) // Assumes you have this API route set up in Laravel
                .then((response) => response.json())
                .then((data) => {
                    console.log("Dynamic data:", data);
                    // Update the DOM with the fetched data
                    // console.log(challenge_data)
                    // problem_no.innerText = no_id;
                    code_input.innerText = data['code'];
                    code_output.innerText = data['code_solution'];
                    code_info.innerText = data['description'];

                    // modal.showModal()
                    modal.showModal();

                });
        }
    </script>
@endsection
