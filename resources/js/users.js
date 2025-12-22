function show_challenge_details(no_id = 0) {
    const modal = document.getElementById("my_modal_2");
    const problem_no = document.getElementById("problem_id");
    const problem_description = document.getElementById("problem_description");

    fetch("/api/get-challenge-details/" + no_id) // Assumes you have this API route set up in Laravel
        .then((response) => response.json())
        .then((data) => {
            console.log("Dynamic data:", data);
            // Update the DOM with the fetched data
            // console.log(challenge_data)
            problem_no.innerText = no_id;
            problem_description.innerText = data[0]["description"];

            // modal.showModal()
            my_modal_2.showModal()
        });
}

document.addEventListener("DOMContentLoaded", () => {
    const details_btns = document.querySelectorAll("#challenge_details_btn")

    details_btns.forEach(btn => {
        btn.addEventListener('click', () => {
            show_challenge_details(btn.value)
        })
    });
});
