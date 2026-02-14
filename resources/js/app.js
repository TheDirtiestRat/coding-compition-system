import "./bootstrap";
import * as monaco from "monaco-editor";

// Declare the editor instance variable globally accessible
let editorInstance;

// Initial state variables
let currentLanguage;
let currentTheme;

/**
 * Initializes the Monaco Editor instance once.
 * @param {string} language - The initial language.
 * @param {string} theme - The initial theme.
 */
function init_code_editor(language, theme) {
    const container = document.getElementById("monaco-editor-container");

    const codevalue =
    `class Main {
    public static void main(String[] args) {
        // Java Code here ...
    }
}`

    if (container) {
        // Create the editor instance ONCE
        editorInstance = monaco.editor.create(container, {
            value: codevalue,
            language: language,
            automaticLayout: true,
            theme: theme,
            minimap: { enabled: false }, // Common setting to save space
            fontSize: 14,
        });

        // Listen for window resize events to update editor layout
        window.addEventListener("resize", () => {
            editorInstance.layout();
        });
    }
}

/**
 * Handles the running of code by sending it to a server endpoint.
 * @param {string} code - The code string from the editor.
 * @param {string} url_code_api - The backend API endpoint for code execution.
 */
async function runServerCode(code, url_code_api) {
    const outputArea = document.getElementById("code_output_area");
    outputArea.textContent = `Running code in ${currentLanguage} on server...`;

    try {
        const response = await fetch(url_code_api, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                // Ensure the CSRF token element exists before trying to access its attribute
                "X-CSRF-TOKEN":
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content") || "",
            },
            body: JSON.stringify({ code: code }),
        });

        const data = await response.json();

        if (response.ok) {
            outputArea.textContent = `${currentLanguage} Output:\n${data.output}`;
        } else {
            // Server-side execution error (e.g., syntax error)
            outputArea.textContent = `Server Error (${currentLanguage}):\n${
                data.error || "Unknown server error"
            }`;
        }
    } catch (error) {
        // Network or fetch error
        outputArea.textContent = `Network/Fetch Error: ${error.message}`;
    }
}

/**
 * Handles client-side execution (for languages like JavaScript).
 * @param {string} code - The code string from the editor.
 */
function runClientCode(code) {
    const outputArea = document.getElementById("code_output_area");
    // Clear previous output
    outputArea.textContent = "";

    try {
        // Simple client-side execution for JS/TS
        // Note: For security and reliable output capture, this is limited.
        new Function(code)();

        outputArea.textContent =
            "Client-side Execution (Check Browser Console for Output)";
    } catch (error) {
        outputArea.textContent = `Execution Error:\n${error.message}`;
        console.error("Client Code Execution Error:", error);
    }
}

function submitCodeForReview(code, id) {
    const outputAlert = document.getElementById("alert_container");
    const outputAlertError = document.getElementById("alert_error");
    const outputAlertSuccess = document.getElementById("alert_success");
    const challenge_id = id;
    const submittedCode = code;

    fetch("/api/submit-code-for-review", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            // Important for Laravel POST requests
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
        },
        body: JSON.stringify({
            challenge_id: challenge_id,
            code: submittedCode,
        }),
    })
        .then((response) => {
            if (!response.ok) {
                outputAlertSuccess.classList.add("hidden");
                outputAlertError.classList.remove("hidden");
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then((data) => {
            // Tell the user that code was submitted successfully
            outputAlert.classList.remove("hidden");
            outputAlert.classList.add("flex");
            const spanAlertSuccess =
                document.getElementById("success_alert_text");
            outputAlertError.classList.add("hidden");
            outputAlertSuccess.classList.remove("hidden");
            spanAlertSuccess.innerText = data.message;
        })
        .catch((error) => {
            console.error("Error:", error);
            outputAlert.classList.remove("hidden");
            outputAlert.classList.add("flex");
            outputAlertSuccess.classList.add("hidden");
            outputAlertError.classList.remove("hidden");
        });
}

// When the candidate has already submitted something,
// we just fetch it again from the hidden field
function initWithSubmittedCode() {
    const submittedCodeEl = document.getElementById("submittedCode");
    if (!submittedCodeEl.value.trim()) return;

    editorInstance.setValue(submittedCodeEl.value)
}

// --- Main DOM Content Loaded Handler ---
document.addEventListener("DOMContentLoaded", () => {
    const languageSelectorElement =
        document.querySelector("#language-selector");
    const themeSelectorElement = document.querySelector("#theme-selector");

    // 1. Get initial values from selectors
    currentLanguage = languageSelectorElement?.value || "python";
    currentTheme = themeSelectorElement?.value || "vs-dark";

    // 2. Initialize the editor with the initial values
    init_code_editor(currentLanguage, currentTheme);

    // Get submitted code
    initWithSubmittedCode();

    // --- Event Listeners for Selectors ---

    // Listener for Language Change
    languageSelectorElement?.addEventListener("change", function (event) {
        currentLanguage = event.target.value;
        if (editorInstance) {
            // Update editor language without destroying the instance
            monaco.editor.setModelLanguage(
                editorInstance.getModel(),
                currentLanguage,
            );
            console.log(`Language changed to: ${currentLanguage}`);
        }
    });

    // Listener for Theme Change
    themeSelectorElement?.addEventListener("change", function (event) {
        currentTheme = event.target.value;
        if (editorInstance) {
            // Update editor theme without re-creating the instance
            editorInstance.updateOptions({ theme: currentTheme });
            console.log(`Theme changed to: ${currentTheme}`);
        }
    });

    // --- Run Button Listener ---
    document.getElementById("run_code_btn")?.addEventListener("click", () => {
        if (!editorInstance) {
            console.error("Monaco Editor not initialized.");
            return;
        }

        const code = editorInstance.getValue();

        // Use the global 'currentLanguage' for execution
        console.log(`Running the code... in ${currentLanguage}`);

        switch (currentLanguage) {
            case "java":
                runServerCode(code, "/run-java-code");
                break;
            case "python":
                runServerCode(code, "/run-python-code");
                break;
            case "javascript": // Explicitly handle client-side languages
            case "typescript":
                runClientCode(code);
                break;
            default:
                // Default to server execution if the language is unknown or needs a backend
                runServerCode(code, "/run-generic-code");
                console.log("Sending code to a generic server endpoint...");
        }
    });

    // run the code submit button
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


