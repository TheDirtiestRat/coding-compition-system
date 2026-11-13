let confirmation_pending = false;

function accept_modal(message) {
    if (confirmation_pending) return Promise.resolve(false);

    const modal = document.getElementById("alert_modal_1");
    document.getElementById("alert_modal_message").innerText = message;
    modal.returnValue = "";
    confirmation_pending = true;

    return new Promise((resolve) => {
        modal.addEventListener("close", () => {
            confirmation_pending = false;
            resolve(modal.returnValue === "accept");
        }, { once: true });
        modal.showModal();
    });
}

function validate_form(form_id, validate_message = "") {
    const forms = document.querySelectorAll("." + form_id);

    forms.forEach((form) => {
        form.addEventListener("submit", async function (event) {
            event.preventDefault();

            if (await accept_modal(validate_message)) {
                form.submit();
            }
        });
    });
}

document.addEventListener("DOMContentLoaded", () => {
    validate_form(
        "candidate_delete_form",
        "Are you sure you want to delete this record?"
    );
});
