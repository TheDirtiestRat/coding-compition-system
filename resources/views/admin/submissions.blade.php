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
        <!-- Submissions List -->
        <div class="basis-1/2 max-h-screen bg-base-100 rounded-xl p-3">
            <p class="mb-2 text-2xl font-medium text-center">Submissions</p>

            <!-- List Container -->
            <div class="flex flex-col gap-2 h-[500px] overflow-y-auto">
                @foreach ($submissions as $submission)
                    @include("components.submission", [$submission])
                @endforeach
            </div>
        </div>

        {{-- Submission Container --}}
        @include("components.submission-placeholder")
        @include("components.submission-display")
    </div>
@endsection

@section('admin-scripts')
    <script>
        let codeLanguage;
        let editorInstance;

        async function view_submission(submission_id) {
            const submissionDisplayContainer = document.getElementById("submissionDisplayContainer");
            const submissionPlaceholderContainer = document.getElementById("submissionPlaceholderContainer");

            const candidateNameEl = document.getElementById("candidateName");
            const challengeTitleEl = document.getElementById("challengeTitle");
            const submittedAtEl = document.getElementById("submittedAt");
            // const codeLanguageEl = document.getElementById("codeLanguage");
            // const copyCodeButton = document.getElementById("copyCodeButton");
            const codeDisplay = document.getElementById("codeDisplay");
            const scoreInputEl = document.getElementById("scoreInput");

            const res = await fetch(`/admin/api/submissions/${submission_id}`)
            if (!res.ok) {
                alert("Failed to display submission.")
                return;
            }

            const { submission } = await res.json();
            if (!submission) {
                alert("Failed to display submission.")
                return;
            }

            candidateNameEl.innerText = submission.candidate_name;
            challengeTitleEl.innerText = submission.challenge_title;
            submittedAtEl.innerText = submission.submitted_at;
            editorInstance.setValue(submission.code);

            submissionDisplayContainer.hidden = false;
            submissionPlaceholderContainer.hidden = true;
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
                readOnly: true
            });

            // Listen for window resize events to update editor layout
            window.addEventListener("resize", () => {
                editorInstance.layout();
            });
        }

        function handleRunningCode() {
            if (!editorInstance) {
                console.error("Monaco Editor not initialized.");
                return;
            }

            const code = editorInstance.getValue();

            console.log(`Running the code... in ${codeLanguage}`);

            switch (currentLanguage) {
                case "java":
                    runServerCode(code, "/run-java-code");
                    break;
                case "python":
                    runServerCode(code, "/run-python-code");
                    break;
                case "javascript":
                case "typescript":
                    runClientCode(code);
                    break;
                default:
                    runServerCode(code, "/run-generic-code");
                    console.log("Sending code to a generic server endpoint...");
            }
        }

        document.addEventListener("DOMContentLoaded", () => {
            initCodeEditor();
        });
    </script>
@endsection
