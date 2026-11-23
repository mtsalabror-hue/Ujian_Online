document.addEventListener("DOMContentLoaded", () => {

    document.querySelectorAll(".dropdown-btn").forEach(button => {

        button.addEventListener("click", function (e) {

            e.preventDefault();

            const submenu = this.nextElementSibling;

            submenu.classList.toggle("show");

        });

    });

});

document.addEventListener("DOMContentLoaded", () => {

    const toggle = document.getElementById("toggleSidebar");

    const sidebar = document.querySelector(".sidebar");

    if (toggle && sidebar) {

        const mobileQuery = window.matchMedia("(max-width: 991.98px)");

        let backdrop = document.querySelector(".sidebar-backdrop");

        if (!backdrop) {

            backdrop = document.createElement("div");

            backdrop.className = "sidebar-backdrop";

            document.body.appendChild(backdrop);

        }

        const closeDrawer = () => {
            sidebar.classList.remove("mobile-open");
            backdrop.classList.remove("show");
        };

        toggle.addEventListener("click", () => {

            if (mobileQuery.matches) {

                sidebar.classList.toggle("mobile-open");

                const open = sidebar.classList.contains("mobile-open");

                backdrop.classList.toggle("show", open);

            } else {

                sidebar.classList.toggle("hide");

            }

        });

        backdrop.addEventListener("click", closeDrawer);

        window.addEventListener("resize", () => {
            if (!mobileQuery.matches) {
                closeDrawer();
            }
        });

    }

});