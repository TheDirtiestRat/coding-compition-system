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

    <div class="space-y-4">
        <div class="flex flex-row gap-3 w-full">
            {{-- forms --}}
            <div class="basis-full h-fit bg-base-100 rounded-xl p-3">
                <form method="POST" action="{{ url('/admin/save-challenge') }}" id="challenge_form"
                    enctype="multipart/form-data">
                    @csrf
                    {{-- form --}}
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Challenge ID</legend>
                        <input type="text" class="input" name="challenge_id" id="challenge_id"
                            value="" placeholder="Assigned when created" readonly />

                        <legend class="fieldset-legend">for waht Event?</legend>
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

                            <fieldset class="fieldset basis-full">
                                <legend class="fieldset-legend">Language</legend>
                                <select class="select" id="language" name="language">
                                    @foreach ($languages as $lang)
                                        <option value="{{ $lang->name }}" data-lang-id="{{ $lang->id }}">{{ ucfirst($lang->name) }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="language_id" id="language_id" value="{{ $languages->first()?->id }}" />
                            </fieldset>

                            <input type="hidden" name="code_solution" id="code_solution">
                        </div>

                        {{-- Multiple Photos --}}
                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">Challenge Photos (optional, multiple)</legend>
                            <input type="file" class="file-input file-input-bordered w-full" name="photos[]" id="photos" accept="image/*" multiple />
                            <div id="photo_preview_container" class="mt-2 flex flex-wrap gap-2"></div>
                            <div id="existing_photos" class="mt-2 flex flex-wrap gap-2"></div>
                        </fieldset>

                        {{-- Test Cases --}}
                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">Test Cases (for auto-grading)</legend>
                            <div id="test_cases_container">
                                <div class="test-case-entry flex flex-row gap-2 items-end mb-2">
                                    <div class="basis-1/4">
                                        <label class="label-text text-xs">Input</label>
                                        <textarea class="textarea textarea-bordered w-full text-sm" name="test_cases[0][input]" placeholder="stdin input"></textarea>
                                    </div>
                                    <div class="basis-1/3">
                                        <label class="label-text text-xs">Expected Output</label>
                                        <textarea class="textarea textarea-bordered w-full text-sm" name="test_cases[0][expected_output]" placeholder="Expected output" required></textarea>
                                    </div>
                                    <div class="basis-1/6">
                                        <label class="label-text text-xs">Points</label>
                                        <input type="number" class="input input-bordered w-full input-sm" name="test_cases[0][points]" value="0" min="0" step="0.5" required />
                                    </div>
                                    <button type="button" class="btn btn-sm btn-error remove-test-case" disabled>-</button>
                                </div>
                            </div>
                            <button type="button" id="add_test_case" class="btn btn-sm btn-ghost mt-1">+ Add Test Case</button>
                        </fieldset>

                        <div class="divider"></div>
                        <button type="submit" id="challenge_submit_btn" class="btn btn-primary">Add Challenge</button>
                    </fieldset>
                </form>
            </div>

            {{-- Code Solution --}}
            <div id="submissionDisplayContainer" class="basis-full flex flex-col h-full shadow rounded-xl">
                <div class="basis-full bg-base-100 p-2 flex flex-row gap-3 w-full items-center rounded-t-xl px-3">
                    <span class="text-xl basis-full">Challenge Code Solution</span>
                    <p id="codeLanguage" class="text-gray-400 font-mono">Java</p>
                    <button onclick="copyCode()" class="btn btn-sm btn-base-100 btn-ghost rounded-xl">Copy Code</button>
                </div>
                <div id="monacoEditorContainer" class="w-full h-[480px]"></div>
                <div class="basis-auto bg-base-100 p-3 flex gap-3 flex-col px-3 h-full rounded-b-3xl">
                    <div class="flex gap-3">
                        <p class="text-gray-400 w-full">Edit the code here ma Rigga!</p>
                        <button id="testCodeBtn" class="btn btn-sm btn-primary basis-2/4">Test Code</button>
                    </div>
                    <div id="code_output_area"
                        class="basis-full bg-gray-400 p-3 rounded-xl w-full whitespace-pre-wrap break-all font-mono">Code
                        Output:
                    </div>
                </div>
            </div>
        </div>
        <x-challenge-list :challenges="$challenges" />
    </div>
@endsection

@section('admin-scripts')
    <script>
        let codeLanguage;
        let editorInstance;

        let testCaseIndex = 1;

        function edit_challenge(challenge_id) {
            const form = document.getElementById('challenge_form')
            const challenge_id_elem = document.getElementById("challenge_id");
            const challenge_event_elem = document.getElementById("event_id");
            const challenge_title = document.getElementById("title");
            const challenge_description = document.getElementById("description");
            const challenge_difficulty = document.getElementById("difficulty");
            const challenge_language = document.getElementById("language");
            const challenge_language_id = document.getElementById("language_id");
            const challenge_points = document.getElementById("points");
            const submitButton = document.getElementById("challenge_submit_btn");

            const codeLanguage = document.getElementById("codeLanguage");

            challenge_id_elem.value = challenge_id

            let challenge_data = {}

            fetch('/admin/api/get-challenge-details/' + challenge_id)
                .then(response => response.json())
                .then(data => {
                    challenge_data = data
                    challenge_event_elem.value = data['event_id']
                    challenge_title.value = data['title']
                    challenge_description.value = data['description']
                    challenge_difficulty.value = data['difficulty']
                    challenge_language.value = data['language']
                    challenge_language_id.value = data['language_id'] || ''
                    challenge_points.value = data['points']

                    // Select the correct language option and update its hidden id
                    const langOptions = challenge_language.options;
                    for (let i = 0; i < langOptions.length; i++) {
                        if (langOptions[i].value === data['language']) {
                            langOptions[i].selected = true;
                            const optLangId = langOptions[i]?.dataset?.langId || data['language_id'] || '';
                            challenge_language_id.value = optLangId;
                            break;
                        }
                    }

                    submitButton.innerText = 'Update Challenge'
                    editorInstance.setValue(data['code_solution']);
                    monaco.editor.setModelLanguage(editorInstance.getModel(), data['language']);
                    codeLanguage.innerText = data['language']

                    // Load existing photos
                    const container = document.getElementById('existing_photos');
                    container.innerHTML = '';
                    if (data['photos_list'] && data['photos_list'].length > 0) {
                        data['photos_list'].forEach(photo => {
                            const wrapper = document.createElement('div');
                            wrapper.className = 'relative group';
                            wrapper.innerHTML = `
                                <img src="${photo.url}" class="h-24 w-24 object-cover rounded-xl" />
                                <label class="absolute top-0 right-0 bg-red-600 text-white text-xs p-1 rounded cursor-pointer opacity-0 group-hover:opacity-100 transition">
                                    <input type="checkbox" name="delete_photos[]" value="${photo.id}" class="hidden" />
                                    ✕
                                </label>
                            `;
                            wrapper.addEventListener('click', function() {
                                const cb = this.querySelector('input[type="checkbox"]');
                                cb.checked = !cb.checked;
                                this.classList.toggle('opacity-50');
                            });
                            container.appendChild(wrapper);
                        });
                    }

                    // Load test cases
                    const tcContainer = document.getElementById('test_cases_container');
                    tcContainer.innerHTML = '';
                    testCaseIndex = 0;
                    if (data['test_cases_list'] && data['test_cases_list'].length > 0) {
                        data['test_cases_list'].forEach((tc, idx) => {
                            addTestCaseEntry(tc, idx);
                            testCaseIndex = idx + 1;
                        });
                    } else {
                        addTestCaseEntry(null, 0);
                        testCaseIndex = 1;
                    }
                });
        }

        function addTestCaseEntry(data = null, index = null) {
            const idx = index !== null ? index : testCaseIndex;
            const container = document.getElementById('test_cases_container');
            const div = document.createElement('div');
            div.className = 'test-case-entry flex flex-row gap-2 items-end mb-2';
            div.innerHTML = `
                <div class="basis-1/4">
                    <label class="label-text text-xs">Input</label>
                    <textarea class="textarea textarea-bordered w-full text-sm" name="test_cases[${idx}][input]" placeholder="stdin input">${data ? (data.input || '') : ''}</textarea>
                </div>
                <div class="basis-1/3">
                    <label class="label-text text-xs">Expected Output</label>
                    <textarea class="textarea textarea-bordered w-full text-sm" name="test_cases[${idx}][expected_output]" placeholder="Expected output" required>${data ? (data.expected_output || '') : ''}</textarea>
                </div>
                <div class="basis-1/6">
                    <label class="label-text text-xs">Points</label>
                    <input type="number" class="input input-bordered w-full input-sm" name="test_cases[${idx}][points]" value="${data ? data.points : 0}" min="0" step="0.5" required />
                </div>
                <button type="button" class="btn btn-sm btn-error remove-test-case">-</button>
            `;
            container.appendChild(div);
            div.querySelector('.remove-test-case').addEventListener('click', function() {
                if (container.children.length > 1) {
                    div.remove();
                }
            });
            if (index === null) testCaseIndex++;
        }

        function copyCode() {
            const code = editorInstance.getValue();
            navigator.clipboard.writeText(code);
            alert("Code copied")
        }

        function initCodeEditor() {
            const container = document.getElementById("monacoEditorContainer");

            if (!container) return;

            editorInstance = monaco.editor.create(container, {
                language: codeLanguage ?? 'java',
                value: `class Main {
    public static void main(String[] args) {
        // code here ...
    }
}`,
                automaticLayout: true,
                theme: 'vs-dark',
                minimap: {
                    enabled: false
                },
                fontSize: 14,
            });

            window.addEventListener("resize", () => {
                editorInstance.layout();
            });
        }

        function attachSubmitChallengeListener() {
            const form = document.getElementById('challenge_form');

            form.addEventListener('submit', event => {
                event.preventDefault();

                const codeSolution = editorInstance.getValue();
                document.getElementById('code_solution').value = codeSolution;

                form.submit();
            });
        }

        async function run_code() {
            const currentLanguage = "Java"
            const code = editorInstance.getValue()
            const url_code_api = "/run-java-code";

            const outputElement = document.getElementById("code_output_area");
            outputElement.textContent = `Testing code in the server...`;

            try {
                const response = await fetch("/run-java-code", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content") || "",
                    },
                    body: JSON.stringify({
                        code: code
                    }),
                });

                const data = await response.json();

                if (response.ok) {
                    outputElement.textContent = `${currentLanguage} Output:\n${data.output}`;
                } else {
                    outputElement.textContent =
                        `Server Error (${currentLanguage}):\n${data.error || "Unknown server error"}`;
                }
            } catch (error) {
                outputElement.textContent = `Network/Fetch Error: ${error.message}`;
            }
        }

        // Sync language selector with hidden language_id
        document.addEventListener("DOMContentLoaded", () => {
            initCodeEditor();
            attachSubmitChallengeListener();

            // Photo preview for multiple files
            const photoInput = document.getElementById('photos');
            const previewContainer = document.getElementById('photo_preview_container');
            photoInput.addEventListener('change', function() {
                previewContainer.innerHTML = '';
                Array.from(this.files).forEach(file => {
                    const reader = new FileReader();
                    const img = document.createElement('img');
                    img.className = 'h-24 w-24 object-cover rounded-xl';
                    reader.onload = e => { img.src = e.target.result; };
                    reader.readAsDataURL(file);
                    previewContainer.appendChild(img);
                });
            });

            // Language selector sync
            const langSelect = document.getElementById('language');
            const langIdInput = document.getElementById('language_id');
            langSelect.addEventListener('change', function() {
                const selectedOpt = this.options[this.selectedIndex];
                const langName = this.value;
                const langId = selectedOpt?.dataset?.langId || '';
                langIdInput.value = langId;
                document.getElementById('codeLanguage').innerText = langName;
                if (editorInstance) {
                    monaco.editor.setModelLanguage(editorInstance.getModel(), langName);
                }
            });

            // Test cases
            document.getElementById('add_test_case').addEventListener('click', function() {
                addTestCaseEntry();
            });

            // Set initial test case remove
            document.querySelectorAll('.remove-test-case').forEach(btn => {
                btn.addEventListener('click', function() {
                    const container = document.getElementById('test_cases_container');
                    if (container.children.length > 1) {
                        this.closest('.test-case-entry').remove();
                    }
                });
            });

            const test_btn = document.getElementById('testCodeBtn');
            test_btn.addEventListener('click', () => {
                run_code();
            })
        });
    </script>
@endsection
