window.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".dropdown").forEach((dropdown) => {
        dropdown.addEventListener("click", (e) => {
            document.querySelectorAll(".dropdown-items").forEach((dropdownItems) => {
                if (dropdownItems !== e.currentTarget.querySelector(".dropdown-items")) {
                    dropdownItems.classList.remove("dropdown-items-active");
                }
            });

            e.currentTarget.querySelector(".dropdown-items")
                .classList.toggle("dropdown-items-active");
        });
    });

    document.querySelector("[navbar-trigger]").addEventListener("click", () => {
        document.querySelector("[navbar-items]").classList.toggle("navbar-items-active");
    });
});