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
        <div class="basis-full flex flex-col bg-base-100 rounded-xl p-3 gap-4">
            <div>
                <div class="flex flex-row justify-between items-center">
                    <p id="candidateName" class="text-2xl">Candidate Name</p>
                    <p id="submittedAt" class="text-sm">Submitted Date</p>
                </div>
                <p id="challengeTitle" class="">Challenge Title</p>
            </div>

            <!-- Code Container -->
            <div class="bg-gray-900 h-full rounded-lg p-4">
                <div class="flex flex-row justify-between items-center mb-3">
                    <p id="codeLanguage" class="text-gray-400 text-sm font-mono">Java</p>
                    <button id="copyCodeButton" class="btn btn-sm btn-ghost">Copy Code</button>
                </div>
                <pre class="text-gray-100 text-sm font-mono overflow-x-auto">
                        <code id="codeDisplay"></code>
                    </pre>
            </div>

            <!-- Submit Input and Button -->
            <div class="space-x-4">
                <label for="score">Score: </label>
                <input id="scoreInput" type="number" name="score" min="0" class="input input-sm max-w-32">
                <button id="submitButton" type="button" class="btn btn-sm btn-primary">Submit</button>
            </div>
        </div>
    </div>
@endsection

@section('admin-scripts')
    <script>
        async function view_submission(submission_id) {
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
        }
    </script>
@endsection
