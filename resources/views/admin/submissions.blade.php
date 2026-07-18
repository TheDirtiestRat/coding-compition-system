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
                const res = await fetch(`/admin/api/submissions/${id}`);
                if (!res.ok) return alert('Failed to load submission');

                const {
                    submission
                } = await res.json();
                if (!submission) return alert('Invalid submission');

                submissionId = submission.id;
                maxPoints = submission.max_points;

                el.candidateName.textContent = submission.candidate_name;
                el.challengeTitle.textContent = submission.challenge_title;
                el.submittedAt.textContent = submission.submitted_at;
                el.solutionLanguage.textContent = submission.challenge_language;

                el.solutionInstruction.textContent = submission.description;

                submissionEditor.setValue(submission.code);
                solutionEditor.setValue(submission.code_solution);

                // Show test case results if available
                const tcContainer = document.getElementById('test_case_results_container');
                const tcList = document.getElementById('test_case_results_list');
                tcList.innerHTML = '';
                if (submission.test_case_results && submission.test_case_results.length > 0) {
                    tcContainer.hidden = false;
                    tcList.innerHTML = '';
                    const results = typeof submission.test_case_results === 'string'
                        ? JSON.parse(submission.test_case_results)
                        : submission.test_case_results;
                    results.forEach((tc, idx) => {
                        const div = document.createElement('div');
                        div.className = `p-2 rounded-xl text-sm ${tc.passed ? 'bg-success/20' : 'bg-error/20'}`;
                        div.innerHTML = `
                            <div class="flex justify-between">
                                <span class="font-semibold">Test Case #${idx + 1}</span>
                                <span class="badge ${tc.passed ? 'badge-success' : 'badge-error'} badge-sm">${tc.passed ? 'PASS' : 'FAIL'}</span>
                            </div>
                            <div class="text-xs mt-1">
                                <div>Input: <code class="bg-base-300 px-1 rounded">${tc.input || '(none)'}</code></div>
                                <div>Expected: <code class="bg-base-300 px-1 rounded">${tc.expected_output}</code></div>
                                <div>Actual: <code class="bg-base-300 px-1 rounded">${tc.actual_output || '(error)'}</code></div>
                                ${tc.error ? `<div class="text-error">Error: ${tc.error}</div>` : ''}
                                <div>Points: ${tc.passed ? tc.points : 0}/${tc.points}</div>
                            </div>
                        `;
                        tcList.appendChild(div);
                    });
                } else {
                    tcContainer.hidden = true;
                }

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

                const score = Number(el.scoreInput.value);
                if (Number.isNaN(score) || score < 0 || score > maxPoints) {
                    // alert(`Score must be between 0 and ${maxPoints}`);
                    show_alert(`Score must be between 0 and ${maxPoints}`)
                    return;
                }

                const res = await fetch(
                    `/admin/api/submissions/${submissionId}/submit-score`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            score
                        }),
                    }
                );

                if (!res.ok) return alert('Failed to submit score');
                window.location.reload();
            });

            // here is the code for the testing of the candidates code
            async function run_code() {
                const currentLanguage = "Java"
                const code = submissionEditor.getValue()
                const url_code_api = "/run-java-code";

                const outputElement = document.getElementById("code_output_area");
                outputElement.textContent = `Testing code in the server...`;

                try {
                    const response = await fetch("/run-java-code", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            // Ensure the CSRF token element exists before trying to access its attribute
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
                        // Server-side execution error (e.g., syntax error)
                        outputElement.textContent =
                            `Server Error (${currentLanguage}):\n${data.error || "Unknown server error"}`;
                    }
                } catch (error) {
                    // Network or fetch error
                    outputElement.textContent = `Network/Fetch Error: ${error.message}`;
                }
            }

            // generate the submission list
            function getSubmissionList(submissions = []) {
                const submissionListArea = document.getElementById('codeSubmissionListElem')
                submissionListArea.innerHTML = '';

                console.log(submissions);
                submissions.submission.forEach((submission) => {
                    let subElement = `<div
                        onclick="view_submission('${submission.id}')"
                        class="bg-base-300 h-fit rounded-md p-3 hover:bg-base-200 hover:cursor-grab">
                        <div class="flex flex-row justify-between items-center">
                            <p class="text-sm">${submission.candidate_name}</p>
                            <p class="badge badge-xs badge-primary">${submission.challenge_difficulty}</p>
                        </div>
                        <p class="">${submission.challenge_title}</p>
                        <p class="mt-2 text-xs">${submission.submitted_at}</p>
                    </div>`
                    submissionListArea.insertAdjacentHTML('beforeend', subElement);
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
                    // console.log(submissions);
                } catch (error) {
                    console.error('Error fetching submissions:', error);
                }
            }, 3000);

        })();
    </script>
@endsection
