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
                        <input type="text" class="input" name="candidate_id" id="candidate_id"
                            placeholder="User Id" readonly disabled />
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
                    <thead class="bg-slate-700/50 text-neutral-200 text-center">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Group</th>
                            <th>Actions</th>
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
                                <td class="flex flex-row items-center justify-center gap-2">
                                    <button class="btn btn-xs"
                                        onclick="edit_candidate('{{ $candidate->id }}','{{ $candidate->fullname }}','{{ optional($candidate->user)->name }}')">Edit</button>
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
@endsection

@section('admin-scripts')
    <script>
        function edit_candidate(user_id, name, username) {
            const edit_tab = document.getElementById("edit_tabs");
            const can_user_id = document.getElementById("candidate_id");
            const can_name = document.getElementById("edit_name");
            const can_username = document.getElementById("edit_username");
            const can_password = document.getElementById("edit_user_password");

            can_user_id.value = user_id
            can_name.value = name;
            can_username.value = username;

            edit_tab.checked = true;
        }
    </script>
@endsection
