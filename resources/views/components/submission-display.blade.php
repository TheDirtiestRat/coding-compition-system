<div id="submissionDisplayContainer" hidden class="basis-full flex flex-col bg-base-100 rounded-xl p-3 gap-4">
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
        <div id="monacoEditorContainer" class="w-full h-[400px]"></div>
    </div>

    <!-- Submit Input and Button -->
    <div class="space-x-4">
        <label for="score">Score: </label>
        <input id="scoreInput" type="number" name="score" min="0" class="input input-sm max-w-32">
        <input id="scoreInput" type="number" name="score" min="0" class="input input-sm max-w-32">
        <button id="submitButton" type="button" class="btn btn-sm btn-primary">Submit</button>
    </div>
</div>
