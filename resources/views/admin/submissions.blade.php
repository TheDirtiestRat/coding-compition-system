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
        <div class="flex flex-col w-full gap-3">
            {{-- Code Instruction --}}
            <div class="basis-auto bg-base-100 px-3 p-2 flex flex-col gap-2 rounded-xl">
                <p class="text-xl basis-full">Challenge Description</p>
                <p id="solutionInstruction" class="text-gray-400 w-full whitespace-pre-wrap break-all"></p>
            </div>

            <div class="basis-full flex flex-row gap-3">
                @include('components.submission-placeholder')
                @include('components.submission-display')

                {{-- Code Solution --}}
                <div class="basis-full flex flex-col shadow rounded-xl">


                    <div class="basis-auto bg-base-100 px-3 p-2 flex gap-2 rounded-t-xl">
                        <p class="text-xl basis-full">Challenge Code Solution</p>
                        <p id="solutionLanguage" class="text-gray-400 font-mono"></p>
                    </div>
                    <div id="solutionEditor" class="h-[500px]"></div>
                    <div class="basis-auto bg-base-100 px-3 p-2 flex gap-2 rounded-b-xl">
                        <span class="text-gray-400 mb-2">
                            the challenge solution code will appear here...
                        </span>
                    </div>
                </div>
            </div>
        </div>



    </div>

    {{-- Test Case Results --}}
    <div id="test_case_results_container" hidden class="bg-base-100 rounded-xl mt-3 p-3 flex flex-col gap-2">
        <p class="text-lg font-medium">Test Case Results</p>
        <div id="test_case_results_list" class="flex flex-col gap-2"></div>
    </div>

    {{-- Score Section --}}
    <div id="scoreSection" hidden class="bg-base-100 rounded-xl mt-3 p-3 flex flex-row gap-3">
        <form id="scoreForm" class="flex items-center gap-3 px-3 ">
            @csrf
            <label class="font-semibold">Score</label>
            <input id="scoreInput" type="number" class="input input-sm max-w-32" required />
            <span id="maxPointsLabel" class="ml-2 text-sm text-gray-500 w-[90px]"></span>
            <button class="btn btn-sm btn-primary ml-4">Submit</button>
        </form>

        <div id="code_output_area"
            class="basis-full bg-gray-400 p-3 rounded-xl w-full font-mono whitespace-pre-wrap break-all">Code Output:</div>
    </div>

    {{-- Submissions --}}
    <div class="bg-base-100 rounded-xl mt-3 p-3">
        <p class="text-2xl font-medium text-center mb-2">Submissions</p>
        <div class="h-[400px] overflow-y-auto">
            <div id="codeSubmissionListElem" class="grid grid-cols-2 gap-2">
                @foreach ($submissions as $submission)
                    @include('components.submission', ['submission' => $submission])
                @endforeach
            </div>
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
            let selectionRequest = 0;
            let currentLanguageId = null;
            let savingScore = false;
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
                solutionInstruction: document.getElementById('solutionInstruction'),
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
                    minimap: {
                        enabled: false
                    },
                    fontSize: 14,
                };
            }

            // -----------------------------
            // Submission Viewer
            // -----------------------------
            window.view_submission = async (id) => {
                const requestId = ++selectionRequest;
                submissionId = null;
                currentLanguageId = null;
                el.scoreInput.value = '';
                el.scoreInput.disabled = true;
                el.scoreSection.hidden = true;
                el.submissionDisplay.hidden = true;
                el.submissionPlaceholder.hidden = false;
                document.getElementById('code_output_area').textContent = 'Code Output:';
                document.getElementById('test_case_results_container').hidden = true;

                try {
                    const res = await fetch(`/admin/api/submissions/${id}`, {
                        headers: { 'Accept': 'application/json' },
                    });
                    if (!res.ok) throw new Error('Failed to load submission.');
                    const { submission } = await res.json();
                    if (requestId !== selectionRequest) return;
                    if (!submission) throw new Error('Submission not found.');

                    submissionId = submission.id;
                    currentLanguageId = submission.challenge_language_id;
                    maxPoints = Number(submission.max_points);
                    el.candidateName.textContent = submission.candidate_name;
                    el.challengeTitle.textContent = submission.challenge_title;
                    el.submittedAt.textContent = submission.submitted_at;
                    el.solutionLanguage.textContent = submission.challenge_language;
                    document.getElementById('codeLanguage').textContent = submission.challenge_language;
                    el.solutionInstruction.textContent = submission.description;
                    submissionEditor.setValue(submission.code);
                    solutionEditor.setValue(submission.code_solution);
                    monaco.editor.setModelLanguage(submissionEditor.getModel(), submission.challenge_language);
                    monaco.editor.setModelLanguage(solutionEditor.getModel(), submission.challenge_language);

                    const results = typeof submission.test_case_results === 'string'
                        ? JSON.parse(submission.test_case_results)
                        : submission.test_case_results || [];
                    const tcList = document.getElementById('test_case_results_list');
                    tcList.replaceChildren();
                    results.forEach((tc, idx) => {
                        const result = document.createElement('div');
                        result.className = `p-2 rounded-xl text-sm whitespace-pre-wrap ${tc.passed ? 'bg-success/20' : 'bg-error/20'}`;
                        result.textContent = `Test Case #${idx + 1}: ${tc.passed ? 'PASS' : 'FAIL'}
Input: ${tc.input ?? '(none)'}
Expected: ${tc.expected_output}
Actual: ${tc.actual_output ?? '(error)'}
Points: ${tc.passed ? tc.points : 0}/${tc.points}${tc.error ? '\nError: ' + tc.error : ''}`;
                        tcList.appendChild(result);
                    });
                    document.getElementById('test_case_results_container').hidden = results.length === 0;
                    el.maxPointsLabel.textContent = `/ ${maxPoints}`;
                    el.scoreInput.max = maxPoints;
                    el.scoreInput.min = 0;
                    el.scoreInput.disabled = submission.is_score_submitted;
                    el.submissionPlaceholder.hidden = true;
                    el.submissionDisplay.hidden = false;
                    el.scoreSection.hidden = false;
                } catch (error) {
                    if (requestId === selectionRequest) show_alert(error.message);
                }
            };

            window.copyCode = () => {
                const code = submissionEditor.getValue();
                navigator.clipboard.writeText(code);
                alert("Code copied")
            }

            function show_alert(message = "") {
                const modal_message_elem = document.getElementById("alert_modal_message");
                modal_message_elem.innerText = message;
                alert_modal_1.showModal();
            }

            // -----------------------------
            // Score Submit
            // -----------------------------
            document.getElementById('scoreForm').addEventListener('submit', async e => {
                e.preventDefault();
                if (!submissionId || el.scoreInput.disabled || savingScore) return;
                const score = Number(el.scoreInput.value);
                if (el.scoreInput.value.trim() === '' || !Number.isInteger(score) || score < 0 || score > maxPoints) {
                    show_alert(`Score must be a whole number between 0 and ${maxPoints}.`);
                    return;
                }

                savingScore = true;
                const button = e.currentTarget.querySelector('button');
                button.disabled = true;
                try {
                    const res = await fetch(`/admin/api/submissions/${submissionId}/submit-score`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ score }),
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.errors?.score?.[0] || data.message || 'Failed to submit score.');
                    window.location.reload();
                } catch (error) {
                    show_alert(error.message);
                } finally {
                    savingScore = false;
                    button.disabled = false;
                }
            });

            // here is the code for the testing of the candidates code
            async function run_code() {
                if (!submissionId || !currentLanguageId) return show_alert("No executable language is configured for this submission.");
                const currentLanguage = el.solutionLanguage.textContent;
                const runRequest = selectionRequest;
                const code = submissionEditor.getValue();

                const outputElement = document.getElementById("code_output_area");
                outputElement.textContent = `Testing code in the server...`;

                try {
                    const response = await fetch("/run-code", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            // Ensure the CSRF token element exists before trying to access its attribute
                            "X-CSRF-TOKEN": document
                                .querySelector('meta[name="csrf-token"]')
                                ?.getAttribute("content") || "",
                        },
                        body: JSON.stringify({
                            code: code,
                            language_id: currentLanguageId
                        }),
                    });

                    const data = await response.json();
                    if (runRequest !== selectionRequest) return;

                    if (response.ok) {
                        outputElement.textContent = `${currentLanguage} Output:\n${data.output}`;
                    } else {
                        // Server-side execution error (e.g., syntax error)
                        outputElement.textContent =
                            `Server Error (${currentLanguage}):\n${data.error || data.message || "Unknown server error"}`;
                    }
                } catch (error) {
                    // Network or fetch error
                    if (runRequest !== selectionRequest) return;
                    outputElement.textContent = `Network/Fetch Error: ${error.message}`;
                }
            }

            // generate the submission list
            function getSubmissionList(submissions) {
                const submissionListArea = document.getElementById('codeSubmissionListElem');
                submissionListArea.replaceChildren();
                submissions.submission.forEach(submission => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'bg-base-300 h-fit rounded-md p-3 hover:bg-base-200 text-left';
                    button.addEventListener('click', () => window.view_submission(submission.id));
                    const name = document.createElement('p');
                    name.className = 'text-sm';
                    name.textContent = submission.candidate_name;
                    const difficulty = document.createElement('p');
                    difficulty.className = 'badge badge-xs badge-primary';
                    difficulty.textContent = submission.challenge_difficulty;
                    const title = document.createElement('p');
                    title.textContent = submission.challenge_title;
                    const time = document.createElement('p');
                    time.className = 'mt-2 text-xs';
                    time.textContent = submission.submitted_at;
                    button.append(name, difficulty, title, time);
                    submissionListArea.appendChild(button);
                });
            }

            // -----------------------------
            // Init
            // -----------------------------
            document.addEventListener('DOMContentLoaded', function() {
                initEditors();

                const test_btn = document.getElementById('testCodeBtn');
                if (test_btn) {
                    test_btn.addEventListener('click', () => {
                        run_code();
                    });
                }
            });

            // refresh the submission list
            setInterval(async () => {
                try {
                    const res = await fetch(`/admin/api/submissions_list`);
                    if (!res.ok) throw new Error('Failed to fetch submissions');

                    const submissions = await res.json();
                    getSubmissionList(submissions);
                } catch (error) {
                    console.error('Error fetching submissions:', error);
                }
            }, 3000);

        })();
    </script>
@endsection
