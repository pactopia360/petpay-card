(() => {
    "use strict";

    const init = () => {
        const form = document.getElementById("commerceBranchForm");

        if (!form) {
            return;
        }

        const dayLabels = Array.from(
            form.querySelectorAll(".commerce-service-day")
        );

        const syncDay = (label) => {
            const input = label.querySelector(
                'input[name="service_days[]"]'
            );

            if (!input) {
                return;
            }

            label.classList.toggle("is-selected", input.checked);
            label.setAttribute(
                "aria-pressed",
                input.checked ? "true" : "false"
            );
        };

        const syncAllDays = () => {
            dayLabels.forEach(syncDay);
        };

        dayLabels.forEach((label) => {
            const input = label.querySelector(
                'input[name="service_days[]"]'
            );

            if (!input || label.dataset.petpayDayBound === "1") {
                return;
            }

            label.dataset.petpayDayBound = "1";
            label.setAttribute("role", "button");
            label.setAttribute("tabindex", "0");

            input.addEventListener("change", () => {
                syncDay(label);
                form.dispatchEvent(
                    new CustomEvent("petpay:branch-days-changed", {
                        bubbles: true,
                    })
                );
            });

            label.addEventListener("keydown", (event) => {
                if (event.key !== "Enter" && event.key !== " ") {
                    return;
                }

                event.preventDefault();
                input.checked = !input.checked;
                input.dispatchEvent(
                    new Event("change", { bubbles: true })
                );
            });
        });

        form.addEventListener("reset", () => {
            window.setTimeout(syncAllDays, 0);
        });

        document.addEventListener("click", (event) => {
            if (!event.target.closest(".commerce-edit-branch")) {
                return;
            }

            window.setTimeout(syncAllDays, 0);
        });

        syncAllDays();
    };

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init, {
            once: true,
        });
    } else {
        init();
    }
})();