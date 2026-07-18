function show_challenge_details(no_id = 0) {
    const modal = document.getElementById("my_modal_2");
    const problem_no = document.getElementById("problem_id");
    const problem_description = document.getElementById("problem_description");
    const photoContainer = document.getElementById("problem_photo_container");

    fetch("/api/get-challenge-details/" + no_id)
        .then((response) => response.json())
        .then((data) => {
            problem_no.innerText = no_id;
            problem_description.innerText = data["description"];

            photoContainer.innerHTML = '';
            if (data["photos_list"] && data["photos_list"].length > 0) {
                photoContainer.classList.remove("hidden");
                data["photos_list"].forEach(photo => {
                    const img = document.createElement('img');
                    img.className = 'max-h-48 rounded-xl object-cover';
                    img.src = photo.url;
                    photoContainer.appendChild(img);
                });
            } else {
                photoContainer.classList.add("hidden");
            }

            my_modal_2.showModal();
        });
}

document.addEventListener("DOMContentLoaded", () => {
    const details_btns = document.querySelectorAll(".challenge_details_btn")

    details_btns.forEach(btn => {
        btn.addEventListener('click', () => {
            show_challenge_details(btn.value)
        })
    });
});
