<div id="submissionDisplayContainer" hidden class="basis-full flex flex-col rounded-xl shadow">
    <div class="bg-base-100 p-2 px-3 flex justify-between items-center rounded-t-xl">
        <p id="challengeTitle" class="text-xl">Challenge Title</p>
        <p id="candidateName" class="text-gray-400">Candidate Name</p>
    </div>
    <div id="monacoEditorContainer" class="h-[500px]"></div>
    <div class="basis-auto bg-base-100 p-2 px-3 flex justify-between items-center rounded-b-xl">
        <p id="codeLanguage" class="text-gray-400 font-mono text-sm">Java</p>
        <p id="submittedAt" class="text-gray-400 text-sm">Submitted Date</p>
        <div class="flex gap-2">
            <button id="copyCodeButton" onclick="copyCode()" class="btn btn-sm btn-ghost">Copy Code</button>
            <button id="testCodeBtn"  class="btn btn-sm btn-primary">Test Code</button>
        </div>
    </div>
</div>

{{-- <div id="submissionDisplayContainer" hidden class="basis-full flex flex-col bg-base-100 rounded-xl p-3 gap-4">
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
            <button id="copyCodeButton" onclick="copyCode()" class="btn btn-sm btn-ghost">Copy Code</button>
        </div>
        <div id="monacoEditorContainer" class="w-full h-[480px]"></div>
    </div>
</div> --}}
