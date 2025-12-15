function show_alert(message = "") {
    const modal_message_elem = document.getElementById("alert_modal_message");
    modal_message_elem.innerText = message
    alert_modal_1.showModal();
}

function accept_modal() {
    const modal_accept_btn = document.getElementById("accept_alert_btn");

    return new Promise(resolve => {
        modal_accept_btn.addEventListener('click', () => {
            resolve()
        }, {once: true})
    })
}

function validate_form(form_id, validate_message = "") {
    const forms = document.querySelectorAll("." + form_id);

    forms.forEach((form) => {
        form.addEventListener("submit", async function (event) {
            event.preventDefault();

            // show the modal to make sure to submit the form
            console.log("Ask if they want to submit.");

            show_alert(validate_message)

            // if they click yes then proceed to submit
            await accept_modal()
            event.target.submit();
        });
    });
}



document.addEventListener("DOMContentLoaded", () => {
    validate_form("candidate_delete_form", "Are you sure you wanna delete this User?");
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
