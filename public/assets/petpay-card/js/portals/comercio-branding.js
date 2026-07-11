(() => {
    "use strict";

    const init = () => {
        const form = document.getElementById("commerceBrandingForm");

        if (!form) {
            return;
        }

        const saveButton = document.getElementById("brandingSaveButton");
        const modal = document.getElementById("brandingHelpModal");
        const modalTitle = document.getElementById("brandingHelpModalTitle");
        const modalText = document.getElementById("brandingHelpModalText");
        const modalClose = document.getElementById("brandingHelpModalClose");

        const items = [
            {
                input: document.getElementById("branding_header_image"),
                preview: document.getElementById("brandingHeaderPreview"),
                wrapper: document.getElementById("brandingHeaderUpload"),
                remove: document.getElementById("brandingRemoveHeader"),
                removeField: document.getElementById("remove_header_image"),
                edit: document.getElementById("brandingEditHeader"),
                branch: document.getElementById("header_branch_id"),
                title: "Imagen de cabecera",
                help: "Usa una imagen horizontal de al menos 1100 x 200 px. Evita texto pegado a los bordes y fotografías pixeladas.",
            },
            {
                input: document.getElementById("branding_icon_image"),
                preview: document.getElementById("brandingIconPreview"),
                wrapper: document.getElementById("brandingIconUpload"),
                remove: document.getElementById("brandingRemoveIcon"),
                removeField: document.getElementById("remove_icon_image"),
                edit: document.getElementById("brandingEditIcon"),
                branch: document.getElementById("icon_branch_id"),
                title: "Imagen de ícono",
                help: "Usa un logotipo cuadrado de al menos 1024 x 1024 px, con fondo limpio y buena legibilidad.",
            },
            {
                input: document.getElementById("branding_listing_image"),
                preview: document.getElementById("brandingListingPreview"),
                wrapper: document.getElementById("brandingListingUpload"),
                remove: document.getElementById("brandingRemoveListing"),
                removeField: document.getElementById("remove_listing_image"),
                edit: document.getElementById("brandingEditListing"),
                branch: document.getElementById("listing_branch_id"),
                title: "Imagen de listado",
                help: "Usa una imagen cuadrada de al menos 1024 x 1024 px que represente claramente tu negocio.",
            },
        ];

        const setDirty = () => {
            saveButton?.classList.add("is-ready");
            saveButton?.removeAttribute("disabled");
        };

        const normalizePreview = (item) => {
            if (!item.preview || !item.wrapper) {
                return;
            }

            const src = item.preview.getAttribute("src");

            if (!src || !src.trim()) {
                item.preview.removeAttribute("src");
                item.preview.hidden = true;
                item.wrapper.classList.remove("has-image");
                item.remove && (item.remove.disabled = true);
                return;
            }

            item.preview.hidden = false;
            item.wrapper.classList.add("has-image");
            item.remove && (item.remove.disabled = false);
        };

        const previewFile = (item) => {
            const file = item.input?.files?.[0];

            if (!file || !item.preview || !item.wrapper) {
                return;
            }

            const reader = new FileReader();

            reader.addEventListener("load", () => {
                item.preview.setAttribute("src", String(reader.result || ""));
                item.preview.hidden = false;
                item.wrapper.classList.add("has-image");

                if (item.removeField) {
                    item.removeField.value = "0";
                }

                if (item.remove) {
                    item.remove.disabled = false;
                }

                setDirty();
            });

            reader.readAsDataURL(file);
        };

        const removeImage = (item) => {
            if (item.input) {
                item.input.value = "";
            }

            if (item.preview) {
                item.preview.removeAttribute("src");
                item.preview.hidden = true;
            }

            item.wrapper?.classList.remove("has-image");

            if (item.removeField) {
                item.removeField.value = "1";
            }

            if (item.branch) {
                item.branch.value = "";
            }

            if (item.remove) {
                item.remove.disabled = true;
            }

            setDirty();
        };

        const openModal = (item) => {
            if (!modal || !modalTitle || !modalText) {
                return;
            }

            modalTitle.textContent = item.title;
            modalText.textContent = item.help;
            modal.hidden = false;
            document.body.style.overflow = "hidden";
        };

        const closeModal = () => {
            if (!modal) {
                return;
            }

            modal.hidden = true;
            document.body.style.overflow = "";
        };

        items.forEach((item) => {
            normalizePreview(item);

            item.input?.addEventListener("change", () => previewFile(item));
            item.edit?.addEventListener("click", () => item.input?.click());
            item.remove?.addEventListener("click", () => removeImage(item));
            item.branch?.addEventListener("change", setDirty);

            item.wrapper
                ?.closest(".commerce-branding-asset")
                ?.querySelector("[data-branding-help]")
                ?.addEventListener("click", () => openModal(item));
        });

        modalClose?.addEventListener("click", closeModal);

        modal?.addEventListener("click", (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape" && modal && !modal.hidden) {
                closeModal();
            }
        });

        form.addEventListener("submit", () => {
            saveButton?.setAttribute("disabled", "disabled");

            if (saveButton) {
                saveButton.textContent = "Guardando...";
            }
        });
    };

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init, { once: true });
    } else {
        init();
    }
})();
