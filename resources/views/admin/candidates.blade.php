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
        <div class="tabs tabs-lift w-[450px]">
            <input type="radio" name="my_tabs_3" class="tab font-medium" aria-label="Add Candidate" checked="checked" />
            <div class="tab-content bg-base-100 border-base-300 p-3">
                {{-- for adding new candidates --}}
                <span class="text-2xl font-bold">Add new Candidate</span>
                <form method="POST" action="{{ url('/admin/add-candidate') }}">
                    @csrf
                    {{-- form --}}
                    <fieldset class="fieldset mt-2">
                        <legend class="fieldset-legend">for waht Event?</legend>
                        <select class="select" name="event_id">
                            @forelse  ($events as $event)
                                @if ($event->isActive)
                                    <option value="{{ $event->id }}" selected>{{ $event->title }}</option>
                                @else
                                    <option value="{{ $event->id }}">{{ $event->title }}</option>
                                @endif
                            @empty
                                <option disabled selected>Pick a Event</option>
                            @endforelse
                        </select>

                        <legend class="fieldset-legend">Candidates name?</legend>
                        <input type="text" class="input" name="candidate_name" placeholder="Type here" />
                        {{-- <p class="label">Optional</p> --}}
                        <legend class="fieldset-legend">Candidates username?</legend>
                        <input type="text" class="input" name="username" placeholder="Type here" />
                        <p class="label">Optional</p>
                        <legend class="fieldset-legend">Candidates password to use?</legend>
                        <input type="text" class="input" name="user_password" placeholder="Type here" />
                        <div class="divider"></div>
                        <button class="btn btn-primary">Add Candidate</button>
                    </fieldset>
                </form>
            </div>

            <input type="radio" name="my_tabs_3" id="edit_tabs" class="tab font-medium" aria-label="Edit Candidate" />
            <div class="tab-content bg-base-100 border-base-300 p-3">
                {{-- for editing candidates details --}}
                <span class="text-2xl font-bold">Edit Candidate</span>
                <form method="POST" action="{{ url('/admin/update-candidate') }}">
                    @csrf
                    {{-- form --}}
                    <fieldset class="fieldset mt-2">
                        <legend class="fieldset-legend text-md">Candidate ID: </legend>
                        <input type="text" class="input" name="candidate_id" id="candidate_id" placeholder="User Id"
                            readonly />

                        <legend class="fieldset-legend">New Event?</legend>
                        <select class="select" name="event_id" id="event_id">
                            @forelse  ($events as $event)
                                @if ($event->isActive)
                                    <option value="{{ $event->id }}" selected>{{ $event->title }}</option>
                                @else
                                    <option value="{{ $event->id }}">{{ $event->title }}</option>
                                @endif
                            @empty
                                <option disabled selected>Pick a Event</option>
                            @endforelse
                        </select>

                        <legend class="fieldset-legend">New name?</legend>
                        <input type="text" class="input" name="edit_name" id="edit_name" placeholder="Type here" />
                        {{-- <p class="label">Optional</p> --}}
                        <legend class="fieldset-legend">New username?</legend>
                        <input type="text" class="input" name="edit_username" id="edit_username"
                            placeholder="Type here" />
                        <p class="label">Optional</p>
                        <legend class="fieldset-legend">New password to use?</legend>
                        <input type="text" class="input" name="edit_user_password" id="edit_user_password"
                            placeholder="Type here" />
                        <div class="divider"></div>
                        <button class="btn btn-primary">Update Details</button>
                    </fieldset>
                </form>
            </div>
        </div>
        {{-- <div class="basis-auto bg-base-100 rounded-xl p-3">


        </div> --}}
        <div class="basis-full bg-base-100 rounded-xl p-3">
            {{-- list of candidates --}}
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <!-- head -->
                    <thead class=" text-neutral-200 text-center">
                        <tr>
                            <th class="bg-slate-700/50 rounded-tl-xl">ID</th>
                            <th class="bg-slate-700/50">Name</th>
                            <th class="bg-slate-700/50">Username</th>
                            <th class="bg-slate-700/50">Group</th>
                            <th class="bg-slate-700/50">Event</th>
                            <th class="bg-slate-700/50 rounded-tr-xl">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($candidates as $candidate)
                            <tr class="text-center">
                                <th>{{ $candidate->id }}</th>
                                <td>{{ $candidate->fullname }}</td>
                                <td>{{ optional($candidate->user)->name }}</td>
                                <td>
                                    <p class="badge badge-primary badge-soft">
                                        {{ $candidate->group }}
                                    </p>
                                </td>
                                <td>{{ $candidate->title }}</td>
                                <td class="flex flex-row items-center justify-center gap-2">
                                    <a href="{{ url('/admin/candidates/view/' . $candidate->id) }}"
                                        class="btn btn-xs btn-info">View</a>
                                    <button class="btn btn-xs"
                                        onclick="edit_candidate('{{ $candidate->id }}','{{ $candidate->event_id }}','{{ $candidate->fullname }}','{{ optional($candidate->user)->name }}')">Edit</button>
                                    <form action="{{ url('/admin/delete-candidate/' . $candidate->id) }}"
                                        class="candidate_delete_form" method="POST">
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
    </div>

    {{-- candidate info --}}
    @isset($can_name, $user_acc, $solved_challenges)
        <div class="flex flex-row gap-3 w-full bg-base-100 rounded-xl p-3 mt-3">
            <div class="p-2 basis-auto flex flex-col gap-2">
                <span class="text-2xl font-bold text-nowrap">Candidate Details</span>
                <strong><span>Name: {{ $can_name->fullname }}</span></strong>
                <span>Username: {{ $user_acc->name }}</span>
                <span>Email: {{ $user_acc->email }}</span>
                <span>Group: {{ $candidate->group }}</span>
            </div>
            <div class="divider lg:divider-horizontal">*</div>
            <div class="p-2 basis-full">
                <span class="text-2xl font-bold">Solved Problems ({{ count($solved_challenges) }})</span>
                {{-- list --}}
                <div class="grid grid-cols-3 gap-3 mt-3">
                    @foreach ($solved_challenges as $challenges)
                        <div class="bg-base-300 h-fit rounded-2xl p-4 hover:bg-base-200 hover:cursor-grab">
                            <div class="flex flex-row justify-between items-center mb-1">
                                <p class="badge badge-xs badge-primary">{{ $challenges->difficulty }}</p>
                                <p class="badge badge-xs badge-primary">Points: {{ $challenges->score }} /
                                    {{ $challenges->points }}</p>
                            </div>
                            <p class="">{{ $challenges->title }}</p>
                            <p class="mt-2 text-xs">Date: {{ $challenges->created_at }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endisset
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

            edit_tab.checked = true;
        }
    </script>
@endsection
