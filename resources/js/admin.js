function show_alert(message = "") {
    const modal_message_elem = document.getElementById("alert_modal_message");
    modal_message_elem.innerText = message;
    alert_modal_1.showModal();
}

function accept_modal() {
    const modal_accept_btn = document.getElementById("accept_alert_btn");

    return new Promise((resolve) => {
        modal_accept_btn.addEventListener(
            "click",
            () => {
                resolve();
            },
            { once: true }
        );
    });
}

function validate_form(form_id, validate_message = "") {
    const forms = document.querySelectorAll("." + form_id);

    forms.forEach((form) => {
        form.addEventListener("submit", async function (event) {
            event.preventDefault();

            // show the modal to make sure to submit the form
            console.log("Ask if they want to submit.");

            show_alert(validate_message);

            // if they click yes then proceed to submit
            await accept_modal();

            event.target.reset();
            event.target.submit();
        });
    });
}

function verify_code_another(code, id) {
    const outputAlert = document.getElementById("alert_container");
    const outputAlertError = document.getElementById("alert_error");
    const outputAlertSuccess = document.getElementById("alert_success");
    const challenge_id = id;
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
            challenge_id: challenge_id,
            submitted_text: submittedCode,
        }),
    })
        .then((response) => response.json())
        .then((data) => {
            console.log(data);
            outputAlert.classList.remove("hidden");
            outputAlert.classList.add("flex");
            if (data.match) {
                // alert("Code Verified!");
                const spanAlertSuccess =
                    document.getElementById("success_alert_text");
                outputAlertError.classList.add("hidden");
                outputAlertSuccess.classList.remove("hidden");
                spanAlertSuccess.innerText = "Code Verified! " + data.message;
            } else {
                // alert("Verification Failed.");
                outputAlertSuccess.classList.add("hidden");
                outputAlertError.classList.remove("hidden");
            }
        })
        .catch((error) => console.error("Error:", error));
}

document.addEventListener("DOMContentLoaded", () => {
    validate_form(
        "candidate_delete_form",
        "Are you sure you wanna delete this User?"
    );
});

// function edit_candidate(user_id, name, username) {
//     const edit_tab = document.getElementById("edit_tabs");
//     const can_user_id = document.getElementById("user_id");
//     const can_name = document.getElementById("edit_name");
//     const can_username = document.getElementById("edit_username");
//     const can_password = document.getElementById("edit_user_password");

//     can_user_id.value = user_id;
//     can_name.value = name;
//     can_username.value = username;

//     edit_tab.checked = true;
// }
