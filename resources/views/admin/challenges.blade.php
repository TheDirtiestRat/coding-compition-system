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
                        <input type="text" class="input" name="challenge_id" id="challenge_id" placeholder="Challenge Id"
                            readonly />
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
                                    <option value="java">Java</option>
                                    <option value="python">Python</option>
                                </select>
                            </fieldset>

                            <input type="hidden" name="code_solution" id="code_solution">
                        </div>
                        <div class="divider"></div>
                        <button type="submit" id="challenge_submit_btn" class="btn btn-primary">Add Challenge</button>
                    </fieldset>
                </form>
            </div>

            {{-- Code Solution --}}
            <div id="submissionDisplayContainer" class="basis-full flex flex-col bg-base-100 rounded-xl p-3 gap-4">
                <div>
                    <p id="candidateName" class="text-2xl">Challenge Code Solution</p>
                </div>

                <!-- Code Container -->
                <div class="bg-gray-900 h-full rounded-lg p-4">
                    <div class="flex flex-row justify-between items-center mb-3">
                        <p id="codeLanguage" class="text-gray-400 text-sm font-mono">Java</p>
                        <button onclick="copyCode()" class="btn btn-sm btn-ghost">Copy Code</button>
                    </div>
                    <div id="monacoEditorContainer" class="w-full h-[480px]"></div>
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

        function edit_challenge(challenge_id) {
            const form = document.getElementById('challenge_form')
            const challenge_id_elem = document.getElementById("challenge_id");
            const challenge_title = document.getElementById("title");
            const challenge_description = document.getElementById("description");
            const challenge_difficulty = document.getElementById("difficulty");
            const challenge_language = document.getElementById("language");
            const challenge_points = document.getElementById("points");
            const submitButton = document.getElementById("challenge_submit_btn");

            const codeLanguage = document.getElementById("codeLanguage");

            challenge_id_elem.value = challenge_id

            let challenge_data = {}

            fetch('/admin/api/get-challenge-details/' + challenge_id)
                .then(response => response.json())
                .then(data => {
                    challenge_data = data
                    console.log('Dynamic data:', data);
                    // Update the DOM with the fetched data
                    // console.log(challenge_data)
                    challenge_title.value = data['title']
                    challenge_description.value = data['description']
                    challenge_difficulty.value = data['difficulty']
                    challenge_language.value = data['language']
                    challenge_points.value = data['points']

                    submitButton.innerText = 'Update Challenge'
                    editorInstance.setValue(data['code_solution']);
                    monaco.editor.setModelLanguage(editorInstance.getModel(), data['language']);
                    codeLanguage.innerText = data['language']
                });
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
                // TODO: Add language column in Challenges table
                langage: codeLanguage ?? 'java',
                automaticLayout: true,
                theme: 'vs-dark',
                minimap: { enabled: false },
                fontSize: 14,
            });

            // Listen for window resize events to update editor layout
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

        document.addEventListener("DOMContentLoaded", () => {
            initCodeEditor();
            attachSubmitChallengeListener();
        });
    </script>
@endsection
