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

    if (container) {
        // Create the editor instance ONCE
        editorInstance = monaco.editor.create(container, {
            value: `class Main {
        public static void main(String[] args) {
            // Example usage of the method
            for(char letters = 'A'; letters <='E'; letters++){
                System.out.print(letters +"  ");
            }
        }
    }`,
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

// code verify function
function verify_code(code) {
    const outputAlert = document.getElementById("alert_container");
    const outputAlertError = document.getElementById("alert_error");
    const outputAlertSuccess = document.getElementById("alert_success");
    const submittedCode = code;

    fetch("/verify-code-challenge", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            // Important for Laravel POST requests
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
        },
        body: JSON.stringify({
            submitted_text: submittedCode,
        }),
    })
        .then((response) => response.json())
        .then((data) => {
            console.log(data);
            outputAlert.classList.remove("hidden")
            outputAlert.classList.add("flex")
            if (data.match) {
                // alert("Code Verified!");
                const spanAlertSuccess = document.getElementById("success_alert_text");
                outputAlertError.classList.add("hidden")
                outputAlertSuccess.classList.remove("hidden")
                spanAlertSuccess.innerText = "Code Verified! " + data.message
            } else {
                // alert("Verification Failed.");
                outputAlertSuccess.classList.add("hidden")
                outputAlertError.classList.remove("hidden")
            }
        })
        .catch((error) => console.error("Error:", error));
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

    // --- Event Listeners for Selectors ---

    // Listener for Language Change
    languageSelectorElement?.addEventListener("change", function (event) {
        currentLanguage = event.target.value;
        if (editorInstance) {
            // Update editor language without destroying the instance
            monaco.editor.setModelLanguage(
                editorInstance.getModel(),
                currentLanguage
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

    // run the code verify button
    document.getElementById("check_code_btn")?.addEventListener("click", () => {
        if (!editorInstance) {
            console.error("Monaco Editor not initialized.");
            return;
        }
        const code = editorInstance.getValue();
        verify_code(code);
    });

    console.log(`Initial Setup: ${currentLanguage} - ${currentTheme}`);
});

// import "./bootstrap";
// import * as monaco from "monaco-editor";

// // Declare the editor instance variable in a scope accessible by the button handler
// let editorInstance;

// function setup_code_editor(language, theme) {
//     const container = document.getElementById("monaco-editor-container");

//     if (container) {
//         editorInstance = monaco.editor.create(container, {
//             // Assign the instance
//             value: 'print("Hello, Laravel with Python!")\nprint(1 + 2)',
//             language: language,
//             automaticLayout: true,
//             theme: theme,
//         });

//         // Listen for resize events to update layout
//         window.addEventListener("resize", () => {
//             editorInstance.layout();
//         });
//     }
// }

// document.addEventListener("DOMContentLoaded", () => {
//     let newLanguage;
//     let newTheme;
//     const languageSelectorElement =
//         document.querySelector("#language-selector");
//     const themeSelectorElement = document.querySelector("#theme-selector");

//     newLanguage = languageSelectorElement.value
//     newTheme = themeSelectorElement.value

//     languageSelectorElement.addEventListener("change", function (event) {
//         // 3. Define the action to take when the value changes
//         newLanguage = event.target.value;
//         // console.log(`The element's value has changed to: ${newLanguage}`);
//         setup_code_editor(newLanguage, newTheme);
//     });
//     themeSelectorElement.addEventListener("change", function (event) {
//         // 3. Define the action to take when the value changes
//         newTheme = event.target.value;
//         // console.log(`The element's value has changed to: ${newTheme}`);
//     });

//     // Add click listener to the run button
//     document.getElementById("run_code_btn")?.addEventListener("click", () => {
//         if (editorInstance) {
//             // run the code base on the language
//             console.log("running the code... in " + newLanguage);
//             switch (newLanguage) {
//                 case "java":
//                     runServerCode(editorInstance.getValue(), "/run-java-code");
//                     console.log("sending code to java server...");
//                     break;
//                 case "python":
//                     runServerCode(
//                         editorInstance.getValue(),
//                         "/run-python-code"
//                     );
//                     console.log("sending code to python server...");
//                     break;
//                 // ... more case statements ...
//                 default:
//                     runClientCode(editorInstance.getValue());
//                     console.log("sending code...");
//             }
//         }
//     });

//     setup_code_editor(newLanguage, newTheme);
//     console.log("the current: " + newLanguage + " - " + newTheme);
// });

// async function runServerCode(code, url_code_api = "/run-python-code") {
//     const outputArea = document.getElementById("code_output_area");
//     outputArea.textContent = "Running Python code on server...";

//     try {
//         const response = await fetch(url_code_api, {
//             // <-- Changed endpoint URL
//             method: "POST",
//             headers: {
//                 "Content-Type": "application/json",
//                 "X-CSRF-TOKEN": document
//                     .querySelector('meta[name="csrf-token"]')
//                     .getAttribute("content"),
//             },
//             body: JSON.stringify({ code: code }),
//         });

//         const data = await response.json();

//         if (response.ok) {
//             outputArea.textContent = `Python Output:\n${data.output}`;
//         } else {
//             // Handle server-side errors (e.g., syntax errors in Python)
//             outputArea.textContent = `Server Error:\n${data.error}`;
//         }
//     } catch (error) {
//         // Handle network or fetch errors
//         outputArea.textContent = `Network Error: ${error.message}`;
//     }
// }

// function runClientCode(code) {
//     const outputArea = document.getElementById("code_output_area");
//     try {
//         // Use a function constructor for slightly safer execution than eval()
//         // Capture the return value or anything logged to the console
//         const scriptFunction = new Function(code);
//         const result = scriptFunction();

//         outputArea.textContent = `Execution Successful. Returned: ${
//             result || "None"
//         }`;
//     } catch (error) {
//         outputArea.textContent = `Execution Error: ${error.message}`;
//         console.error(error);
//     }
// }

// // New function to handle server-side execution via Fetch API
// // async function runServerPythonCode(code) {
// //     const outputArea = document.getElementById("code_output_area");
// //     outputArea.textContent = "Running Python code on server...";

// //     try {
// //         const response = await fetch("/run-python-code", {
// //             // <-- Changed endpoint URL
// //             method: "POST",
// //             headers: {
// //                 "Content-Type": "application/json",
// //                 "X-CSRF-TOKEN": document
// //                     .querySelector('meta[name="csrf-token"]')
// //                     .getAttribute("content"),
// //             },
// //             body: JSON.stringify({ code: code }),
// //         });

// //         const data = await response.json();

// //         if (response.ok) {
// //             outputArea.textContent = `Python Output:\n${data.output}`;
// //         } else {
// //             // Handle server-side errors (e.g., syntax errors in Python)
// //             outputArea.textContent = `Server Error:\n${data.error}`;
// //         }
// //     } catch (error) {
// //         // Handle network or fetch errors
// //         outputArea.textContent = `Network Error: ${error.message}`;
// //     }
// // }

// // good for security run this if server is in docker
// // New function to handle server-side execution via Fetch API for secure Python endpoint
// // async function runServerPythonCode(code) {
// //     // ... (logic to display 'Running Python code on server...') ...
// //     try {
// //         // <-- Target the secure endpoint URL
// //         const response = await fetch("/run-python-code-secure", {
// //             // ... (headers including CSRF token) ...
// //             body: JSON.stringify({ code: code }),
// //         });
// //         // ... (logic to handle response/error) ...
// //     } catch (error) {
// //         // ... (handle network or fetch errors) ...
// //     }
// // }
