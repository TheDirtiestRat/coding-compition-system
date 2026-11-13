import "./bootstrap";
let monaco;

let editorInstance;
let currentLanguage;
let currentLanguageId;
let currentTheme;
let languages = [];

function init_code_editor(language, theme) {
    const container = document.getElementById("monaco-editor-container");

    const codevalue = language === "java" ?
    `class Main {
    public static void main(String[] args) {
        // Code here ...
    }
}` : "";

    if (container) {
        editorInstance = monaco.editor.create(container, {
            value: codevalue,
            language: language,
            automaticLayout: true,
            theme: theme,
            minimap: { enabled: false },
            fontSize: 14,
        });

        window.addEventListener("resize", () => {
            editorInstance.layout();
        });
    }
}

async function runServerCode(code, languageId) {
    const outputArea = document.getElementById("code_output_area");
    outputArea.textContent = `Running code in ${currentLanguage} on server...`;

    try {
        const response = await fetch("/run-code", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN":
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content") || "",
            },
            body: JSON.stringify({ code: code, language_id: languageId }),
        });

        const data = await response.json();

        if (response.ok) {
            outputArea.textContent = `${currentLanguage} Output:\n${data.output}`;
        } else {
            const errors = Object.values(data.errors || {}).flat().join("\n");
            outputArea.textContent = `Server Error (${currentLanguage}):\n${
                errors || data.error || data.message || "Unknown server error"
            }`;
        }
    } catch (error) {
        outputArea.textContent = `Network/Fetch Error: ${error.message}`;
    }
}

async function submitCodeForReview(code, id) {
    const button = document.getElementById("submit_code_btn");
    const outputArea = document.getElementById("code_output_area");
    button.disabled = true;
    outputArea.textContent = "Submitting code...";

    try {
        const response = await fetch("/api/submit-code-for-review", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify({
                challenge_id: id,
                code: code,
            }),
        });
        const data = await response.json();
        if (!response.ok) {
            const errors = Object.values(data.errors || {}).flat().join("\n");
            throw new Error(errors || data.message || data.error || `Submission failed (${response.status}).`);
        }
        window.location.reload();
    } catch (error) {
        outputArea.textContent = `Submission failed: ${error.message}`;
    } finally {
        button.disabled = false;
    }
}

function initWithSubmittedCode() {
    const submittedCodeEl = document.getElementById("submittedCode");
    if (!submittedCodeEl?.value) return;

    editorInstance.setValue(submittedCodeEl.value);
}

function populateLanguageSelector() {
    const selector = document.querySelector("#language-selector");
    if (!selector || languages.length === 0) return;

    selector.innerHTML = '';
    const challengeLanguageId = selector.dataset.languageId;
    const challengeLanguage = selector.dataset.language;
    languages.filter(lang => lang.is_active && (!challengeLanguage ||
        (challengeLanguageId ? String(lang.id) === challengeLanguageId : lang.name === challengeLanguage)))
        .forEach(lang => {
            const option = document.createElement('option');
            option.value = lang.name;
            option.dataset.langId = lang.id;
            option.textContent = lang.name.charAt(0).toUpperCase() + lang.name.slice(1);
            selector.appendChild(option);
        });

    currentLanguage = selector.value;
    currentLanguageId = selector.options[selector.selectedIndex]?.dataset?.langId;
}

document.addEventListener("DOMContentLoaded", async () => {
    if (!document.getElementById("monaco-editor-container")) return;

    monaco = await import("monaco-editor");
    const languageSelectorElement = document.querySelector("#language-selector");
    const themeSelectorElement = document.querySelector("#theme-selector");

    // Fetch languages from DB
    try {
        const langRes = await fetch('/api/get-languages');
        if (langRes.ok) {
            languages = await langRes.json();
        }
    } catch (e) {
        console.error('Failed to fetch languages', e);
        languages = [];
    }

    populateLanguageSelector();

    currentLanguage = languageSelectorElement?.value || languageSelectorElement?.dataset.language || "java";
    currentTheme = themeSelectorElement?.value || "vs-dark";

    init_code_editor(currentLanguage, currentTheme);

    initWithSubmittedCode();

    const runButton = document.getElementById("run_code_btn");
    runButton.disabled = !currentLanguageId;
    if (!currentLanguageId) {
        document.getElementById("code_output_area").textContent = "The challenge language is unavailable. Contact the administrator.";
    }

    languageSelectorElement?.addEventListener("change", function (event) {
        currentLanguage = event.target.value;
        currentLanguageId = this.options[this.selectedIndex]?.dataset?.langId || null;
        runButton.disabled = !currentLanguageId;
        if (editorInstance) {
            monaco.editor.setModelLanguage(
                editorInstance.getModel(),
                currentLanguage,
            );
        }
    });

    themeSelectorElement?.addEventListener("change", function (event) {
        currentTheme = event.target.value;
        if (editorInstance) {
            editorInstance.updateOptions({ theme: currentTheme });
        }
    });

    runButton.addEventListener("click", async () => {
        if (!editorInstance) {
            console.error("Monaco Editor not initialized.");
            return;
        }

        const code = editorInstance.getValue();
        runButton.disabled = true;
        try {
            await runServerCode(code, currentLanguageId);
        } finally {
            runButton.disabled = false;
        }
    });

    document
        .getElementById("submit_code_btn")
        ?.addEventListener("click", (event) => {
            if (!editorInstance) {
                console.error("Monaco Editor not initialized.");
                return;
            }
            const code = editorInstance.getValue();

            submitCodeForReview(code, event.currentTarget.value);
        });

});
