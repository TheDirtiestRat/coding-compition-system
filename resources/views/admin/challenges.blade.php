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
                            value="{{ old('challenge_id') }}" placeholder="Assigned when created" readonly />

                        <legend class="fieldset-legend">for waht Event?</legend>
                        <select class="select" name="event_id" id="event_id">
                            @forelse  ($events as $event)
                                <option value="{{ $event->id }}" @selected((string) old('event_id', $events->firstWhere('isActive', true)?->id) === (string) $event->id)>{{ $event->title }}</option>
                            @empty
                                <option disabled selected>Pick a Event</option>
                            @endforelse
                        </select>

                        <legend class="fieldset-legend">Challenge Title</legend>
                        <input type="text" class="input w-full" name="title" id="title" value="{{ old('title') }}" placeholder="Type here" />
                        <legend class="fieldset-legend">Description</legend>
                        <textarea class="textarea h-[200px] w-full" name="description" id="description"
                            placeholder="What is the Challenge about?">{{ old('description') }}</textarea>
                        <p class="label">Optional</p>

                        <div class="flex flex-row gap-3">
                            <fieldset class="fieldset w-full">
                                <legend class="fieldset-legend">Equivalent Points</legend>
                                <input type="number" class="input w-full" name="points" id="points" value="{{ old('points') }}"
                                    placeholder="Type here" />
                            </fieldset>
                            <fieldset class="fieldset basis-full">
                                <legend class="fieldset-legend">Difficulty</legend>
                                <select class="select" id="difficulty" name="difficulty">
                                    <option value="easy" @selected(old('difficulty', 'easy') === 'easy')>Easy</option>
                                    <option value="medium" @selected(old('difficulty', 'easy') === 'medium')>Medium</option>
                                    <option value="hard" @selected(old('difficulty', 'easy') === 'hard')>Hard</option>
                                    <option value="challenge" @selected(old('difficulty', 'easy') === 'challenge')>Challenge</option>
                                </select>
                            </fieldset>

                            <fieldset class="fieldset basis-full">
                                <legend class="fieldset-legend">Language</legend>
                                <select class="select" id="language" name="language">
                                    @foreach ($languages as $lang)
                                        <option value="{{ $lang->name }}" data-lang-id="{{ $lang->id }}" @selected(old('language', $languages->first()?->name) === $lang->name)>{{ ucfirst($lang->name) }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="language_id" id="language_id" value="{{ old('language_id', $languages->first()?->id) }}" />
                            </fieldset>

                            <input type="hidden" name="code_solution" id="code_solution">
                        </div>

                        {{-- Multiple Photos --}}
                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">Challenge Photos (optional, multiple)</legend>
                            <input type="file" class="file-input file-input-bordered w-full" name="photos[]" id="photos" accept="image/*" multiple />
                            @if (session()->hasOldInput())
                                <p class="label">If you selected new photos, please select them again before saving.</p>
                            @endif
                            <div id="photo_preview_container" class="mt-2 flex flex-wrap gap-2"></div>
                            <div id="existing_photos" class="mt-2 flex flex-wrap gap-2"></div>
                        </fieldset>

                        {{-- Test Cases --}}
                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">Test Cases (for auto-grading)</legend>
                            <input type="hidden" name="test_cases_present" value="1" />
                            <div id="test_cases_container"></div>
                            <button type="button" id="add_test_case" class="btn btn-sm btn-ghost mt-1">+ Add Test Case</button>
                        </fieldset>

                        <div class="divider"></div>
                        <button type="submit" id="challenge_submit_btn" class="btn btn-primary">{{ old('challenge_id') ? 'Update Challenge' : 'Add Challenge' }}</button>
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
        const challengeDraft = {{ Illuminate\Support\Js::from(session()->getOldInput()) }};
        const draftPhotos = {{ Illuminate\Support\Js::from($draftPhotos->map(fn ($photo) => ['id' => $photo->id, 'url' => $photo->url])->values()) }};
        let codeLanguage;
        let editorInstance;

        let testCaseIndex = 0;
        let selectionRequest = 0;

        function edit_challenge(challenge_id) {
            const requestId = ++selectionRequest;
            const challenge_id_elem = document.getElementById("challenge_id");
            const challenge_event_elem = document.getElementById("event_id");
            const challenge_title = document.getElementById("title");
            const challenge_description = document.getElementById("description");
            const challenge_difficulty = document.getElementById("difficulty");
            const challenge_language = document.getElementById("language");
            const challenge_language_id = document.getElementById("language_id");
            const challenge_points = document.getElementById("points");
            const submitButton = document.getElementById("challenge_submit_btn");
            submitButton.disabled = true;

            const codeLanguage = document.getElementById("codeLanguage");

            fetch('/admin/api/get-challenge-details/' + challenge_id)
                .then(response => {
                    if (!response.ok) throw new Error('Failed to load challenge.');
                    return response.json();
                })
                .then(data => {
                    if (requestId !== selectionRequest) return;

                    if (challenge_id_elem.value !== String(data.id)) {
                        document.getElementById('photos').value = '';
                        document.getElementById('photo_preview_container').innerHTML = '';
                    }
                    challenge_id_elem.value = data.id;
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

                    renderExistingPhotos(data['photos_list']);

                    // Load test cases
                    const tcContainer = document.getElementById('test_cases_container');
                    tcContainer.innerHTML = '';
                    testCaseIndex = 0;
                    if (data['test_cases_list'] && data['test_cases_list'].length > 0) {
                        data['test_cases_list'].forEach((tc, idx) => {
                            addTestCaseEntry(tc, idx);
                            testCaseIndex = idx + 1;
                        });
                    }
                    submitButton.disabled = false;
                })
                .catch(error => {
                    if (requestId === selectionRequest) alert(error.message);
                });
        }

        function renderExistingPhotos(photos = [], deletedIds = []) {
            const container = document.getElementById('existing_photos');
            const deletedPhotoIds = Array.isArray(deletedIds) ? deletedIds.map(String) : [];
            container.innerHTML = '';
            if (photos && photos.length > 0) {
                photos.forEach(photo => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'relative group';
                    wrapper.innerHTML = `
                        <img class="h-24 w-24 object-cover rounded-xl" />
                        <label class="absolute top-0 right-0 bg-red-600 text-white text-xs p-1 rounded cursor-pointer opacity-0 group-hover:opacity-100 transition">
                            <input type="checkbox" name="delete_photos[]" class="hidden" />
                            ✕
                        </label>
                    `;
                    wrapper.querySelector('img').src = photo.url;
                    wrapper.querySelector('input').value = photo.id;
                    wrapper.querySelector('input').checked = deletedPhotoIds.includes(String(photo.id));
                    wrapper.classList.toggle('opacity-50', wrapper.querySelector('input').checked);
                    wrapper.addEventListener('click', function(event) {
                        event.preventDefault();
                        const cb = this.querySelector('input[type="checkbox"]');
                        cb.checked = !cb.checked;
                        this.classList.toggle('opacity-50');
                    });
                    container.appendChild(wrapper);
                });
            }
        }

        function addTestCaseEntry(data = null, index = null) {
            const idx = index !== null ? index : testCaseIndex;
            const container = document.getElementById('test_cases_container');
            const div = document.createElement('div');
            div.className = 'test-case-entry flex flex-row gap-2 items-end mb-2';
            div.innerHTML = `
                <div class="basis-1/4">
                    <label class="label-text text-xs">Input</label>
                    <textarea class="textarea textarea-bordered w-full text-sm" name="test_cases[${idx}][input]" placeholder="stdin input"></textarea>
                </div>
                <div class="basis-1/3">
                    <label class="label-text text-xs">Expected Output</label>
                    <textarea class="textarea textarea-bordered w-full text-sm" name="test_cases[${idx}][expected_output]" placeholder="Expected output" required></textarea>
                </div>
                <div class="basis-1/6">
                    <label class="label-text text-xs">Points</label>
                    <input type="number" class="input input-bordered w-full input-sm" name="test_cases[${idx}][points]" value="0" min="0" step="0.5" required />
                </div>
                <button type="button" class="btn btn-sm btn-error remove-test-case">-</button>
            `;
            div.querySelector('[name$="[input]"]').value = data?.input ?? '';
            div.querySelector('[name$="[expected_output]"]').value = data?.expected_output ?? '';
            div.querySelector('[name$="[points]"]').value = data?.points ?? 0;
            container.appendChild(div);
            div.querySelector('.remove-test-case').addEventListener('click', function() {
                div.remove();
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
                language: document.getElementById('language').value || 'java',
                value: Object.hasOwn(challengeDraft, 'code_solution') ? (challengeDraft.code_solution ?? '') : `class Main {
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
                if (document.getElementById('challenge_submit_btn').disabled) return;

                const codeSolution = editorInstance.getValue();
                document.getElementById('code_solution').value = codeSolution;

                form.submit();
            });
        }

        async function run_code() {
            const currentLanguage = document.getElementById("language").value;
            const languageId = document.getElementById("language_id").value;
            const code = editorInstance.getValue();

            const outputElement = document.getElementById("code_output_area");
            outputElement.textContent = `Testing code in the server...`;

            try {
                const response = await fetch("/run-code", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content") || "",
                    },
                    body: JSON.stringify({
                        code: code,
                        language_id: languageId
                    }),
                });

                const data = await response.json();

                if (response.ok) {
                    outputElement.textContent = `${currentLanguage} Output:\n${data.output}`;
                } else {
                    outputElement.textContent =
                        `Server Error (${currentLanguage}):\n${data.error || data.message || "Unknown server error"}`;
                }
            } catch (error) {
                outputElement.textContent = `Network/Fetch Error: ${error.message}`;
            }
        }

        // Sync language selector with hidden language_id
        document.addEventListener("DOMContentLoaded", () => {
            initCodeEditor();
            attachSubmitChallengeListener();
            document.getElementById('codeLanguage').innerText = document.getElementById('language').value;
            if (Object.keys(challengeDraft).length > 0) {
                document.getElementById('test_cases_container').innerHTML = '';
                testCaseIndex = 0;
                Object.values(challengeDraft.test_cases ?? {}).forEach(tc => addTestCaseEntry(tc));
                renderExistingPhotos(draftPhotos, challengeDraft.delete_photos ?? []);
            }

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

            const test_btn = document.getElementById('testCodeBtn');
            test_btn.addEventListener('click', () => {
                run_code();
            })
        });
    </script>
@endsection
