(() => {
    "use strict";

    const initialize = () => {
        const menus = document.querySelectorAll("[data-commerce-account-menu]");

        menus.forEach((menu) => {
            const summary = menu.querySelector("summary");

            if (summary) {
                summary.setAttribute("aria-expanded", menu.open ? "true" : "false");
            }

            menu.addEventListener("toggle", () => {
                if (summary) {
                    summary.setAttribute("aria-expanded", menu.open ? "true" : "false");
                }
            });
        });

        document.addEventListener("click", (event) => {
            menus.forEach((menu) => {
                if (menu.open && !menu.contains(event.target)) {
                    menu.open = false;
                }
            });
        });

        document.addEventListener("keydown", (event) => {
            if (event.key !== "Escape") {
                return;
            }

            menus.forEach((menu) => {
                menu.open = false;
            });
        });
    };

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initialize, { once: true });
    } else {
        initialize();
    }
})();
