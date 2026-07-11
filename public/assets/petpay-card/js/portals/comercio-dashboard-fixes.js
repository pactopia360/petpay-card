(() => {
    "use strict";

    const ready = (callback) => {
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", callback, { once: true });
            return;
        }

        callback();
    };

    ready(() => {
        const byId = (id) => document.getElementById(id);

        const normalize = (value) => {
            return String(value || "")
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "")
                .toLowerCase()
                .trim();
        };

        const setValue = (id, value, dispatch = false) => {
            const element = byId(id);

            if (!element) {
                return;
            }

            element.value = value ?? "";

            if (dispatch) {
                element.dispatchEvent(new Event("input", { bubbles: true }));
                element.dispatchEvent(new Event("change", { bubbles: true }));
            }
        };

        const setHint = (element, message, valid = null) => {
            if (!element) {
                return;
            }

            element.textContent = message || "";
            element.classList.toggle("is-valid", valid === true);
            element.classList.toggle("is-invalid", valid === false);
        };

        /*
         * Tabs
         */
        const tabButtons = Array.from(document.querySelectorAll("[data-commerce-tab-button]"));
        const tabPanels = Array.from(document.querySelectorAll("[data-commerce-tab-panel]"));

        tabButtons.forEach((button) => {
            if (button.dataset.petpayBound === "1") {
                return;
            }

            button.dataset.petpayBound = "1";

            button.addEventListener("click", () => {
                const tab = button.dataset.commerceTabButton;

                if (!tab) {
                    return;
                }

                tabButtons.forEach((item) => item.classList.remove("is-active"));
                tabPanels.forEach((panel) => panel.classList.remove("is-active"));

                button.classList.add("is-active");
                document
                    .querySelector(`[data-commerce-tab-panel="${CSS.escape(tab)}"]`)
                    ?.classList.add("is-active");

                const url = new URL(window.location.href);
                url.searchParams.set("tab", tab);
                window.history.replaceState({}, "", url.toString());
            });
        });

        /*
         * Contact form
         */
        const contactForm = byId("commerceContactForm");

        if (contactForm) {
            const methodInput = byId("commerceContactMethod");
            const saveButton = byId("commerceSaveButton");
            const resetButton = byId("commerceResetButton");
            const phoneInput = byId("phone");
            const emailInput = byId("email");
            const phoneVerified = byId("phone_verified");
            const emailVerified = byId("email_verified");
            const phoneHint = byId("phoneHint");
            const emailHint = byId("emailHint");

            const fieldIds = [
                "first_name",
                "last_name_paternal",
                "last_name_maternal",
                "street",
                "neighborhood",
                "postal_code",
                "state",
                "phone",
                "email",
            ];

            const resetContactForm = () => {
                contactForm.action = contactForm.dataset.storeAction || contactForm.action;

                if (methodInput) {
                    methodInput.value = "POST";
                }

                fieldIds.forEach((id) => setValue(id, ""));

                if (phoneVerified) {
                    phoneVerified.value = "0";
                }

                if (emailVerified) {
                    emailVerified.value = "0";
                }

                setHint(phoneHint, "");
                setHint(emailHint, "");

                if (saveButton) {
                    saveButton.textContent = "Guardar";
                }

                if (resetButton) {
                    resetButton.hidden = true;
                }
            };

            phoneInput?.addEventListener("input", () => {
                phoneInput.value = phoneInput.value.replace(/\D+/g, "").slice(0, 10);

                if (phoneVerified) {
                    phoneVerified.value = "0";
                }

                setHint(phoneHint, "");
            });

            emailInput?.addEventListener("input", () => {
                if (emailVerified) {
                    emailVerified.value = "0";
                }

                setHint(emailHint, "");
            });

            byId("verifyPhoneButton")?.addEventListener("click", () => {
                const phone = String(phoneInput?.value || "").replace(/\D+/g, "").slice(0, 10);

                if (phoneInput) {
                    phoneInput.value = phone;
                }

                if (!/^\d{10}$/.test(phone)) {
                    if (phoneVerified) {
                        phoneVerified.value = "0";
                    }

                    setHint(phoneHint, "El telefono debe tener 10 digitos.", false);
                    return;
                }

                if (phoneVerified) {
                    phoneVerified.value = "1";
                }

                setHint(phoneHint, "Telefono validado.", true);
            });

            byId("verifyEmailButton")?.addEventListener("click", () => {
                const email = String(emailInput?.value || "").trim();

                if (emailInput) {
                    emailInput.value = email;
                }

                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    if (emailVerified) {
                        emailVerified.value = "0";
                    }

                    setHint(emailHint, "Ingresa un correo valido.", false);
                    return;
                }

                if (emailVerified) {
                    emailVerified.value = "1";
                }

                setHint(emailHint, "Correo validado.", true);
            });

            document.addEventListener("click", (event) => {
                const button = event.target.closest(".commerce-edit-contact");

                if (!button) {
                    return;
                }

                event.preventDefault();

                contactForm.action =
                    button.dataset.updateAction ||
                    contactForm.dataset.storeAction ||
                    contactForm.action;

                if (methodInput) {
                    methodInput.value = "PUT";
                }

                setValue("first_name", button.dataset.firstName);
                setValue("last_name_paternal", button.dataset.lastNamePaternal);
                setValue("last_name_maternal", button.dataset.lastNameMaternal);
                setValue("street", button.dataset.street);
                setValue("neighborhood", button.dataset.neighborhood);
                setValue("postal_code", button.dataset.postalCode);
                setValue("state", button.dataset.state);
                setValue("phone", button.dataset.phone);
                setValue("email", button.dataset.email);

                if (phoneVerified) {
                    phoneVerified.value = button.dataset.phoneVerified || "0";
                }

                if (emailVerified) {
                    emailVerified.value = button.dataset.emailVerified || "0";
                }

                setHint(
                    phoneHint,
                    phoneVerified?.value === "1" ? "Telefono validado." : "",
                    phoneVerified?.value === "1" ? true : null
                );

                setHint(
                    emailHint,
                    emailVerified?.value === "1" ? "Correo validado." : "",
                    emailVerified?.value === "1" ? true : null
                );

                if (saveButton) {
                    saveButton.textContent = "Actualizar";
                }

                if (resetButton) {
                    resetButton.hidden = false;
                }

                contactForm.scrollIntoView({ behavior: "smooth", block: "start" });
            });

            resetButton?.addEventListener("click", resetContactForm);
        }

        /*
         * Contact filters, pagination and CSV export
         */
        const cards = Array.from(document.querySelectorAll("[data-commerce-user-card]"));
        const searchInput = byId("commerceUserSearch");
        const yearSelect = byId("commerceUserYear");
        const statusSelect = byId("commerceUserStatus");
        const typeSelect = byId("commerceUserType");
        const versionSelect = byId("commerceUserVersion");
        const emptyMessage = byId("commerceUserFilterEmpty");
        const downloadButton = byId("commerceUserDownloadButton");
        const contactsContainer = document.querySelector(".commerce-contacts");
        const pageSize = 5;
        let currentPage = 1;

        const pagination = document.createElement("nav");
        pagination.className = "commerce-contact-pagination";
        pagination.setAttribute("aria-label", "Paginacion de contactos");
        pagination.hidden = cards.length <= pageSize;

        if (contactsContainer && cards.length > 0) {
            contactsContainer.insertAdjacentElement("afterend", pagination);
        }

        const getFilteredCards = () => {
            const search = normalize(searchInput?.value);
            const year = String(yearSelect?.value || "");
            const status = String(statusSelect?.value || "");
            const type = normalize(typeSelect?.value);
            const version = String(versionSelect?.value || "");

            return cards.filter((card) => {
                const matchesSearch = !search || normalize(card.dataset.search).includes(search);
                const matchesYear = !year || card.dataset.year === year;
                const matchesStatus = !status || card.dataset.status === status;
                const matchesType = !type || normalize(card.dataset.type).includes(type);
                const matchesVersion = !version || card.dataset.version === version;

                return matchesSearch && matchesYear && matchesStatus && matchesType && matchesVersion;
            });
        };

        const renderPagination = (filteredCards) => {
            const totalPages = Math.max(1, Math.ceil(filteredCards.length / pageSize));

            if (currentPage > totalPages) {
                currentPage = totalPages;
            }

            pagination.innerHTML = "";
            pagination.hidden = filteredCards.length <= pageSize;

            const previous = document.createElement("button");
            previous.type = "button";
            previous.className = "commerce-contact-pagination__button";
            previous.textContent = "Anterior";
            previous.disabled = currentPage <= 1;
            previous.addEventListener("click", () => {
                currentPage -= 1;
                applyFilters();
            });

            const summary = document.createElement("span");
            summary.className = "commerce-contact-pagination__summary";
            summary.textContent = `Pagina ${currentPage} de ${totalPages}`;

            const next = document.createElement("button");
            next.type = "button";
            next.className = "commerce-contact-pagination__button";
            next.textContent = "Siguiente";
            next.disabled = currentPage >= totalPages;
            next.addEventListener("click", () => {
                currentPage += 1;
                applyFilters();
            });

            pagination.append(previous, summary, next);
        };

        const applyFilters = () => {
            const filteredCards = getFilteredCards();
            const start = (currentPage - 1) * pageSize;
            const end = start + pageSize;

            cards.forEach((card) => {
                card.hidden = true;
            });

            filteredCards.slice(start, end).forEach((card) => {
                card.hidden = false;
            });

            if (emptyMessage) {
                emptyMessage.hidden = filteredCards.length > 0;
            }

            renderPagination(filteredCards);
        };

        [searchInput, yearSelect, statusSelect, typeSelect, versionSelect].forEach((control) => {
            control?.addEventListener(control.tagName === "INPUT" ? "input" : "change", () => {
                currentPage = 1;
                applyFilters();
            });
        });

        const escapeCsv = (value) => {
            const text = String(value ?? "");
            return `"${text.replace(/"/g, '""')}"`;
        };

        downloadButton?.addEventListener("click", () => {
            const filteredCards = getFilteredCards();

            if (filteredCards.length === 0) {
                window.alert("No hay contactos para exportar.");
                return;
            }

            const rows = [
                [
                    "Nombre",
                    "Direccion",
                    "Telefono",
                    "Correo",
                    "Principal",
                    "Telefono verificado",
                    "Correo verificado",
                    "Actualizado",
                ],
                ...filteredCards.map((card) => [
                    card.dataset.name,
                    card.dataset.address,
                    card.dataset.phone,
                    card.dataset.email,
                    card.dataset.primary,
                    card.dataset.phoneVerifiedLabel,
                    card.dataset.emailVerifiedLabel,
                    card.dataset.updatedLabel,
                ]),
            ];

            const csv = "\uFEFF" + rows.map((row) => row.map(escapeCsv).join(",")).join("\r\n");
            const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
            const url = URL.createObjectURL(blob);
            const link = document.createElement("a");

            link.href = url;
            link.download = `contactos-comercio-${new Date().toISOString().slice(0, 10)}.csv`;
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(url);
        });

        applyFilters();

        /*
         * Branch basic fixes
         */
        const branchForm = byId("commerceBranchForm");

        if (branchForm) {
            const methodInput = byId("commerceBranchMethod");
            const resetButton = byId("branchResetButton");
            const saveButton = byId("branchSaveButton");
            const visibilityInput = byId("branch_is_open");
            const visibilitySwitch = byId("branchServiceSwitch");
            const visibilityText = byId("branchServiceText");

            const updateVisibility = (visible) => {
                if (visibilityInput) {
                    visibilityInput.value = visible ? "1" : "0";
                }

                visibilitySwitch?.classList.toggle("is-on", visible);
                visibilitySwitch?.classList.toggle("is-off", !visible);

                if (visibilityText) {
                    visibilityText.textContent = visible ? "Visible" : "Oculta";
                }
            };

            visibilitySwitch?.addEventListener("click", () => {
                updateVisibility(visibilityInput?.value !== "1");
            });

            const resetBranchForm = () => {
                branchForm.reset();
                branchForm.action = branchForm.dataset.storeAction || branchForm.action;

                if (methodInput) {
                    methodInput.value = "POST";
                }

                setValue("branch_phone_verified", "0");
                setValue("branch_email_verified", "0");
                updateVisibility(true);

                if (saveButton) {
                    saveButton.setAttribute("aria-label", "Guardar sucursal");
                    saveButton.setAttribute("data-tooltip", "Guardar sucursal");
                }

                const actionHint = byId("branchActionHint");

                if (actionHint) {
                    actionHint.textContent = "Acciones";
                }

                if (resetButton) {
                    resetButton.hidden = true;
                }
            };

            window.petpayEditCommerceBranch = (button) => {
                if (!button) {
                    return false;
                }

                branchForm.action =
                    button.dataset.updateAction ||
                    branchForm.dataset.storeAction ||
                    branchForm.action;

                if (methodInput) {
                    methodInput.value = "PUT";
                }

                setValue("branch_chain_name", button.dataset.chainName);
                setValue("branch_branch_name", button.dataset.branchName);
                setValue("branch_branch_code", button.dataset.branchCode);
                setValue("branch_google_coordinates", button.dataset.googleCoordinates, true);
                setValue("branch_location_search", button.dataset.googleCoordinates, true);
                setValue("branch_street", button.dataset.street);
                setValue("branch_neighborhood", button.dataset.neighborhood);
                setValue("branch_postal_code", button.dataset.postalCode);
                setValue("branch_state", button.dataset.state);
                setValue("branch_phone", button.dataset.phone);
                setValue("branch_email", button.dataset.email);
                setValue("branch_website", button.dataset.website);
                setValue("branch_whatsapp_phone", button.dataset.whatsappPhone);
                setValue("branch_service_open_time", button.dataset.serviceOpenTime);
                setValue("branch_service_close_time", button.dataset.serviceCloseTime);
                setValue("branch_phone_verified", button.dataset.phoneVerified || "0");
                setValue("branch_email_verified", button.dataset.emailVerified || "0");

                let serviceDays = [];

                try {
                    serviceDays = JSON.parse(button.dataset.serviceDays || "[]");
                } catch (error) {
                    serviceDays = [];
                }

                branchForm
                    .querySelectorAll('input[name="service_days[]"]')
                    .forEach((input) => {
                        input.checked = serviceDays.includes(input.value);
                    });

                updateVisibility((button.dataset.isOpen || "1") === "1");

                if (saveButton) {
                    saveButton.setAttribute("aria-label", "Actualizar sucursal");
                    saveButton.setAttribute("data-tooltip", "Actualizar sucursal");
                }

                const actionHint = byId("branchActionHint");

                if (actionHint) {
                    actionHint.textContent = "Actualizar sucursal";
                }

                if (resetButton) {
                    resetButton.hidden = false;
                }

                branchForm.scrollIntoView({ behavior: "smooth", block: "start" });
                return false;
            };

            document.addEventListener(
                "click",
                (event) => {
                    const button = event.target.closest(".commerce-edit-branch");

                    if (!button) {
                        return;
                    }

                    event.preventDefault();
                    event.stopPropagation();
                    window.petpayEditCommerceBranch(button);
                },
                true
            );

            resetButton?.addEventListener("click", resetBranchForm);
        }
    });
})();

