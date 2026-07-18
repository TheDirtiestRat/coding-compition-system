@extends('layouts.admin')

@section('admin-content')
    @if (session('success') || session('error') || $errors->any())
        <div id="alert_container" class="w-full flex-col gap-1 flex mb-3">
            @if (session('success'))
                <div role="alert" class="alert alert-success w-full flex">
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div role="alert" class="alert alert-error w-full flex">
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            @if ($errors->any())
                <div role="alert" class="alert alert-error w-full flex">
                    <ul class="p-2 px-3 rounded-b-xl">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    <div class="space-y-4">
        <div class="bg-base-100 rounded-xl p-3">
            <form method="POST" action="{{ url('/admin/programming-languages/store') }}" id="language_form">
                @csrf
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Programming Language Details</legend>

                    <legend class="fieldset-legend">Name</legend>
                    <input type="text" class="input w-full" name="name" id="name" placeholder="e.g. java, python" required />

                    <legend class="fieldset-legend">File Extension</legend>
                    <input type="text" class="input w-full" name="file_extension" id="file_extension" placeholder="e.g. .java, .py" required />

                    <legend class="fieldset-legend">Compile Command (optional)</legend>
                    <input type="text" class="input w-full" name="compile_command" id="compile_command" placeholder="e.g. javac {file}" />
                    <p class="label-text text-xs text-gray-500">Use <code>{file}</code> as placeholder for the source file path.</p>

                    <legend class="fieldset-legend">Run Command</legend>
                    <input type="text" class="input w-full" name="run_command" id="run_command" placeholder="e.g. java -cp {dir} Main" required />
                    <p class="label-text text-xs text-gray-500">Use <code>{dir}</code> as placeholder for the temp directory, <code>{file}</code> for the file (without extension).</p>

                    <div class="divider"></div>
                    <button type="submit" class="btn btn-primary">Add Language</button>
                </fieldset>
            </form>
        </div>

        <div class="bg-base-100 rounded-xl overflow-hidden p-3">
            <table class="table table-zebra">
                <thead>
                    <tr class="text-center text-neutral-200">
                        <th class="bg-slate-700/50 rounded-tl-xl">ID</th>
                        <th class="bg-slate-700/50">Name</th>
                        <th class="bg-slate-700/50">Extension</th>
                        <th class="bg-slate-700/50">Compile Command</th>
                        <th class="bg-slate-700/50">Run Command</th>
                        <th class="bg-slate-700/50">Active</th>
                        <th class="bg-slate-700/50 rounded-tr-xl">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($languages as $lang)
                        <tr class="text-center">
                            <th>{{ $lang->id }}</th>
                            <td>{{ $lang->name }}</td>
                            <td>{{ $lang->file_extension }}</td>
                            <td class="max-w-xs truncate">{{ $lang->compile_command ?? '-' }}</td>
                            <td class="max-w-xs truncate">{{ $lang->run_command }}</td>
                            <td>
                                @if ($lang->is_active)
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    <span class="badge badge-ghost">No</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-xs" onclick="edit_language({{ $lang->id }})">Edit</button>
                                <form action="{{ url('/admin/programming-languages/delete/' . $lang->id) }}" method="POST" class="inline">
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

    {{-- Edit Modal --}}
    <dialog id="edit_language_modal" class="modal">
        <div class="modal-box w-[600px]">
            <h3 class="text-lg font-bold">Edit Language</h3>
            <form method="POST" action="{{ url('/admin/programming-languages/update') }}">
                @csrf
                <input type="hidden" name="id" id="edit_id">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Name</legend>
                    <input type="text" class="input w-full" name="name" id="edit_name" required />

                    <legend class="fieldset-legend">File Extension</legend>
                    <input type="text" class="input w-full" name="file_extension" id="edit_file_extension" required />

                    <legend class="fieldset-legend">Compile Command</legend>
                    <input type="text" class="input w-full" name="compile_command" id="edit_compile_command" />

                    <legend class="fieldset-legend">Run Command</legend>
                    <input type="text" class="input w-full" name="run_command" id="edit_run_command" required />

                    <div class="modal-action">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <button type="button" class="btn" onclick="edit_language_modal.close()">Cancel</button>
                    </div>
                </fieldset>
            </form>
        </div>
    </dialog>
@endsection

@section('admin-scripts')
    <script>
        function edit_language(id) {
            fetch('/admin/api/get-languages')
                .then(r => r.json())
                .then(languages => {
                    const lang = languages.find(l => l.id === id);
                    if (!lang) return;
                    document.getElementById('edit_id').value = lang.id;
                    document.getElementById('edit_name').value = lang.name;
                    document.getElementById('edit_file_extension').value = lang.file_extension;
                    document.getElementById('edit_compile_command').value = lang.compile_command || '';
                    document.getElementById('edit_run_command').value = lang.run_command;
                    document.getElementById('edit_language_modal').showModal();
                });
        }
    </script>
@endsection
