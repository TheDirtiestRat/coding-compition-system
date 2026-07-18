import "./bootstrap";
import * as monaco from "monaco-editor";

let editorInstance;
let currentLanguage;
let currentLanguageId;
let currentTheme;
let languages = [];

function init_code_editor(language, theme) {
    const container = document.getElementById("monaco-editor-container");

    const codevalue =
    `class Main {
    public static void main(String[] args) {
        // Code here ...
    }
}`

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
            outputArea.textContent = `Server Error (${currentLanguage}):\n${
                data.error || "Unknown server error"
            }`;
        }
    } catch (error) {
        outputArea.textContent = `Network/Fetch Error: ${error.message}`;
    }
}

function submitCodeForReview(code, id) {
    const challenge_id = id;

    fetch("/api/submit-code-for-review", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
        },
        body: JSON.stringify({
            challenge_id: challenge_id,
            code: code,
        }),
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then((data) => {
            window.location.reload();
        })
        .catch((error) => {
            console.error("Error:", error);
        });
}

function initWithSubmittedCode() {
    const submittedCodeEl = document.getElementById("submittedCode");
    if (!submittedCodeEl.value.trim()) return;

    editorInstance.setValue(submittedCodeEl.value)
}

function populateLanguageSelector() {
    const selector = document.querySelector("#language-selector");
    if (!selector || languages.length === 0) return;

    selector.innerHTML = '';
    languages.forEach(lang => {
        const option = document.createElement('option');
        option.value = lang.name;
        option.dataset.langId = lang.id;
        option.textContent = lang.name.charAt(0).toUpperCase() + lang.name.slice(1);
        selector.appendChild(option);
    });

    currentLanguage = selector.value;
    currentLanguageId = selector.options[selector.selectedIndex]?.dataset?.langId || languages[0]?.id;
}

document.addEventListener("DOMContentLoaded", async () => {
    const languageSelectorElement = document.querySelector("#language-selector");
    const themeSelectorElement = document.querySelector("#theme-selector");

    // Fetch languages from DB
    try {
        const langRes = await fetch('/api/get-languages');
        if (langRes.ok) {
            languages = await langRes.json();
        } else {
            // Try admin endpoint as fallback for admin pages
            const adminRes = await fetch('/admin/api/get-languages');
            if (adminRes.ok) languages = await adminRes.json();
        }
    } catch (e) {
        console.error('Failed to fetch languages', e);
        languages = [];
    }

    populateLanguageSelector();

    currentLanguage = languageSelectorElement?.value || "java";
    currentTheme = themeSelectorElement?.value || "vs-dark";

    init_code_editor(currentLanguage, currentTheme);

    initWithSubmittedCode();

    languageSelectorElement?.addEventListener("change", function (event) {
        currentLanguage = event.target.value;
        currentLanguageId = this.options[this.selectedIndex]?.dataset?.langId || null;
        if (editorInstance) {
            monaco.editor.setModelLanguage(
                editorInstance.getModel(),
                currentLanguage,
            );
            console.log(`Language changed to: ${currentLanguage}`);
        }
    });

    themeSelectorElement?.addEventListener("change", function (event) {
        currentTheme = event.target.value;
        if (editorInstance) {
            editorInstance.updateOptions({ theme: currentTheme });
            console.log(`Theme changed to: ${currentTheme}`);
        }
    });

    document.getElementById("run_code_btn")?.addEventListener("click", () => {
        if (!editorInstance) {
            console.error("Monaco Editor not initialized.");
            return;
        }

        const code = editorInstance.getValue();
        console.log(`Running the code... in ${currentLanguage}`);

        runServerCode(code, currentLanguageId);
    });

    document
        .getElementById("submit_code_btn")
        ?.addEventListener("click", (event) => {
            if (!editorInstance) {
                console.error("Monaco Editor not initialized.");
                return;
            }
            const code = editorInstance.getValue();

            submitCodeForReview(code, event.target.value);
        });

    console.log(`Initial Setup: ${currentLanguage} - ${currentTheme}`);
});
