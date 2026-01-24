@extends('layouts.admin')

@section('admin-content')
    {{-- Alerts --}}
    @if (session('success') || session('error') || $errors->any())
        <div class="w-full flex flex-col gap-1 mb-3">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-error">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error">
                    <ul class="list-disc ml-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    {{-- Main Content --}}
    <div class="flex gap-3 w-full">
        @include('components.submission-placeholder')
        @include('components.submission-display')

        {{-- Code Solution --}}
        <div class="basis-full bg-base-100 rounded-xl p-3">
            <p class="text-2xl mb-2">Challenge Code Solution</p>

            <div class="bg-gray-900 rounded-lg p-4">
                <p id="solutionLanguage" class="text-gray-400 text-sm font-mono mb-2"></p>
                <div id="solutionEditor" class="h-[500px]"></div>
            </div>
        </div>
    </div>

    {{-- Score Section --}}
    <div id="scoreSection" hidden class="bg-base-100 rounded-xl mt-3 p-3">
        <form id="scoreForm">
            @csrf
            <label class="font-semibold">Score</label>

            <input id="scoreInput" type="number" class="input input-sm max-w-32" required />

            <span id="maxPointsLabel" class="ml-2 text-sm text-gray-500"></span>

            <button class="btn btn-sm btn-primary ml-4">Submit</button>
        </form>
    </div>

    {{-- Submissions --}}
    <div class="bg-base-100 rounded-xl mt-3 p-3">
        <p class="text-2xl font-medium text-center mb-2">Submissions</p>

        <div class="grid grid-cols-2 gap-2 h-[500px] overflow-y-auto">
            @foreach ($submissions as $submission)
                @include('components.submission', [$submission])
            @endforeach
        </div>
    </div>
@endsection

@section('admin-scripts')
    <script>
        (() => {
            // -----------------------------
            // State
            // -----------------------------
            let submissionId = null;
            let maxPoints = null;
            let submissionEditor = null;
            let solutionEditor = null;

            // -----------------------------
            // DOM
            // -----------------------------
            const el = {
                submissionDisplay: document.getElementById('submissionDisplayContainer'),
                submissionPlaceholder: document.getElementById('submissionPlaceholderContainer'),
                candidateName: document.getElementById('candidateName'),
                challengeTitle: document.getElementById('challengeTitle'),
                submittedAt: document.getElementById('submittedAt'),
                scoreSection: document.getElementById('scoreSection'),
                scoreInput: document.getElementById('scoreInput'),
                maxPointsLabel: document.getElementById('maxPointsLabel'),
                solutionLanguage: document.getElementById('solutionLanguage'),
            };

            // -----------------------------
            // Editors
            // -----------------------------
            function initEditors() {
                submissionEditor = monaco.editor.create(
                    document.getElementById('monacoEditorContainer'),
                    editorConfig()
                );

                solutionEditor = monaco.editor.create(
                    document.getElementById('solutionEditor'),
                    editorConfig()
                );

                window.addEventListener('resize', () => {
                    submissionEditor.layout();
                    solutionEditor.layout();
                });
            }

            function editorConfig() {
                return {
                    language: 'java',
                    theme: 'vs-dark',
                    readOnly: true,
                    automaticLayout: true,
                    minimap: { enabled: false },
                    fontSize: 14,
                };
            }

            // -----------------------------
            // Submission Viewer
            // -----------------------------
            window.view_submission = async (id) => {
                const res = await fetch(`/admin/api/submissions/${id}`);
                if (!res.ok) return alert('Failed to load submission');

                const { submission } = await res.json();
                if (!submission) return alert('Invalid submission');

                submissionId = submission.id;
                maxPoints = submission.max_points;

                el.candidateName.textContent = submission.candidate_name;
                el.challengeTitle.textContent = submission.challenge_title;
                el.submittedAt.textContent = submission.submitted_at;
                el.solutionLanguage.textContent = submission.challenge_language;

                submissionEditor.setValue(submission.code);
                solutionEditor.setValue(submission.code_solution);

                monaco.editor.setModelLanguage(
                    submissionEditor.getModel(),
                    submission.challenge_language
                );
                monaco.editor.setModelLanguage(
                    solutionEditor.getModel(),
                    submission.challenge_language
                );

                el.maxPointsLabel.textContent = `/ ${maxPoints}`;
                el.scoreInput.disabled = submission.is_score_submitted;

                el.submissionPlaceholder.hidden = true;
                el.submissionDisplay.hidden = false;
                el.scoreSection.hidden = false;
            };

            window.copyCode = () => {
                const code = submissionEditor.getValue();
                navigator.clipboard.writeText(code);
                alert("Code copied")
            }

            // -----------------------------
            // Score Submit
            // -----------------------------
            document.getElementById('scoreForm').addEventListener('submit', async e => {
                e.preventDefault();

                const score = Number(el.scoreInput.value);
                if (Number.isNaN(score) || score < 0 || score > maxPoints) {
                    alert(`Score must be between 0 and ${maxPoints}`);
                    return;
                }

                const res = await fetch(
                    `/admin/api/submissions/${submissionId}/submit-score`,
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ score }),
                    }
                );

                if (!res.ok) return alert('Failed to submit score');
                window.location.reload();
            });

            // -----------------------------
            // Init
            // -----------------------------
            document.addEventListener('DOMContentLoaded', initEditors);
        })();
    </script>
@endsection
