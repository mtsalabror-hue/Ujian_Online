document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".toggle-password").forEach(toggle => {
        toggle.addEventListener("click", function () {
            const input = this.closest(".cbt-input-group").querySelector("input");
            if (input) {
                if (input.type === "password") {
                    input.type = "text";
                    this.classList.remove("bi-eye");
                    this.classList.add("bi-eye-slash");
                } else {
                    input.type = "password";
                    this.classList.remove("bi-eye-slash");
                    this.classList.add("bi-eye");
                }
            }
        });
    });
});
