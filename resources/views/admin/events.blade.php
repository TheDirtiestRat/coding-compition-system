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
                <span class="text-2xl font-bold">Add new Event</span>
                <form method="POST" action="{{ url('/admin/add-event') }}">
                    @csrf
                    {{-- form --}}
                    <fieldset class="fieldset mt-2">
                        <legend class="fieldset-legend">Event Title?</legend>
                        <input type="text" class="input" name="event_title" placeholder="Type here" />

                        <button class="btn btn-primary">Add Event</button>
                    </fieldset>
                </form>
            </div>

            <input type="radio" name="my_tabs_3" id="edit_tabs" class="tab font-medium" aria-label="Edit Candidate" />
            <div class="tab-content bg-base-100 border-base-300 p-3">
                {{-- for editing candidates details --}}
                <span class="text-2xl font-bold">Edit Event</span>
                <form method="POST" action="{{ url('/admin/update-event') }}">
                    @csrf
                    {{-- form --}}
                    <fieldset class="fieldset mt-2">
                        <legend class="fieldset-legend text-md">Event ID: </legend>
                        <input type="text" class="input" name="event_id" id="edit_event_id" placeholder="Event Id"
                            readonly />
                        <legend class="fieldset-legend">New Title?</legend>
                        <input type="text" class="input" name="edit_title" id="edit_title" placeholder="Type here" />
                        
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
                            <th class="bg-slate-700/50">Title</th>
                            <th class="bg-slate-700/50">Status</th>
                            <th class="bg-slate-700/50">Actions</th>
                            
                            <th class="bg-slate-700/50">Created At</th>
                            <th class="bg-slate-700/50 rounded-tr-xl">Updated At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($events as $event)
                            <tr class="text-center">
                                <th>{{ $event->id }}</th>
                                <td>{{ $event->title }}</td>

                                <td>
                                    <form action="{{ url('/admin/active-event/') }}" class="" method="POST">
                                        @csrf
                                        <input type="number" class="hidden" name="event_id" value="{{ $event->id }}" readonly/>
                                        @if ($event->isActive)
                                            <button type="submit" name="isActive" value="true"
                                                class="btn btn-xs btn-success">Active</button>
                                        @else
                                            <button type="submit" name="isActive" value="false"
                                                class="btn btn-xs btn-secondary">Deactive</button>
                                        @endif
                                    </form>

                                </td>
                                
                                <td class="flex flex-row items-center justify-center gap-2">
                                    <button class="btn btn-xs"
                                        onclick="edit_event_input('{{ $event->id }}','{{ $event->title }}')">Edit</button>
                                    <form action="{{ url('/admin/delete-event/' . $event->id) }}"
                                        class="candidate_delete_form" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-error">Delete</button>
                                    </form>
                                </td>

                                <td>{{ $event->created_at }}</td>
                                <td>{{ $event->updated_at }}</td>
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
        function edit_event_input(ev_id, title) {
            const edit_tab = document.getElementById("edit_tabs");
            const edit_event_id = document.getElementById("edit_event_id");
            const event_title = document.getElementById("edit_title");

            console.log(ev_id);

            edit_event_id.value = ev_id;
            event_title.value = title;

            edit_tab.checked = true;
        }
    </script>
@endsection
