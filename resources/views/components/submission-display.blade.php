<div id="submissionDisplayContainer" hidden class="admin-card min-w-0 flex flex-col overflow-hidden">
    <div class="bg-base-100 p-5 flex flex-wrap gap-3 justify-between items-center rounded-t-xl">
        <p id="challengeTitle" class="min-w-0 text-base font-semibold wrap-anywhere">Challenge Title</p>
        <p id="candidateName" class="admin-muted">Candidate Name</p>
    </div>
    <div id="monacoEditorContainer" class="h-[500px]"></div>
    <div class="basis-auto bg-base-100 p-5 flex flex-wrap gap-3 justify-between items-center rounded-b-xl">
        <p id="codeLanguage" class="admin-muted font-mono text-sm">Java</p>
        <p id="submittedAt" class="admin-muted text-sm">Submitted Date</p>
        <div class="flex gap-2">
            <button id="copyCodeButton" onclick="copyCode()" class="btn btn-sm btn-ghost">Copy Code</button>
            <button id="testCodeBtn"  class="btn btn-sm btn-primary">Test Code</button>
        </div>
    </div>
</div>
