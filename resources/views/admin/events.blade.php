@extends('layouts.admin')

@section('admin-title', 'Event details')
@section('admin-description', 'Create events and choose the active competition.')

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
            <input type="radio" name="my_tabs_3" class="tab font-medium" aria-label="Add Event" checked="checked" />
            <div class="tab-content bg-base-100 border-base-300 p-5">
                {{-- for adding new candidates --}}
                <h2 class="text-lg font-semibold">Add event</h2>
                <form method="POST" action="{{ url('/admin/add-event') }}">
                    @csrf
                    {{-- form --}}
                    <fieldset class="fieldset mt-2">
                        <label class="fieldset-legend" for="add_events_event_title">Event title</label>
                        <input id="add_events_event_title" type="text" class="input" name="event_title" placeholder="Type here" />

                        <button class="btn btn-primary">Add Event</button>
                    </fieldset>
                </form>
            </div>

            <input type="radio" name="my_tabs_3" id="edit_tabs" class="tab font-medium" aria-label="Edit Event" />
            <div class="tab-content bg-base-100 border-base-300 p-5">
                {{-- for editing candidates details --}}
                <h2 class="text-lg font-semibold">Edit Event</h2>
                <form method="POST" action="{{ url('/admin/update-event') }}">
                    @csrf
                    {{-- form --}}
                    <fieldset class="fieldset mt-2">
                        <label class="fieldset-legend" for="edit_event_id">Event ID: </label>
                        <input type="text" class="input" name="event_id" id="edit_event_id" placeholder="Event Id"
                            readonly />
                        <label class="fieldset-legend" for="edit_title">Event title</label>
                        <input type="text" class="input" name="edit_title" id="edit_title" placeholder="Type here" />
                        
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
                            <th class="bg-base-200">Title</th>
                            <th class="bg-base-200">Status</th>
                            <th class="bg-base-200">Actions</th>
                            
                            <th class="bg-base-200">Created At</th>
                            <th class="bg-base-200 rounded-tr-xl">Updated At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($events as $event)
                            <tr class="text-center">
                                <th>{{ $event->id }}</th>
                                <td>{{ $event->title }}</td>

                                <td>
                                    <form action="{{ url('/admin/active-event/') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="event_id" value="{{ $event->id }}" />
                                        @if ($event->isActive)
                                            <button type="submit" name="isActive" value="true"
                                                class="btn btn-xs btn-success">Active</button>
                                        @else
                                            <button type="submit" name="isActive" value="true"
                                                class="btn btn-xs btn-secondary">Activate</button>
                                        @endif
                                    </form>
                                </td>
                                
                                <td class="flex flex-row items-center justify-center gap-2">
                                    <button class="btn btn-xs"
                                        data-id="{{ $event->id }}" data-title="{{ $event->title }}"
                                        onclick="edit_event_input(this.dataset.id, this.dataset.title)">Edit</button>
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
                        @empty
                            <tr><td colspan="6" class="admin-muted">No events yet. Add an event to get started.</td></tr>
                        @endforelse
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

            edit_event_id.value = ev_id;
            event_title.value = title;

            edit_tab.checked = true;
        }
    </script>
@endsection
