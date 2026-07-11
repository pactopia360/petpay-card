(() => {
    "use strict";

    const money = new Intl.NumberFormat("es-MX", { style: "currency", currency: "MXN" });
    const dateTime = new Intl.DateTimeFormat("es-MX", { dateStyle: "medium", timeStyle: "short" });

    const init = () => {
        const root = document.querySelector("[data-finance-root]");

        if (!root) return;

        const endpoint = root.dataset.endpoint;
        const exportUrl = root.dataset.exportUrl;
        const filters = Array.from(root.querySelectorAll("[data-finance-filter]"));
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || "";

        const escapeHtml = (value) => String(value ?? "")
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#039;");

        const formatDate = (value) => {
            if (!value) return "Sin fecha";
            const parsed = new Date(value);
            return Number.isNaN(parsed.getTime()) ? String(value) : dateTime.format(parsed);
        };

        const queryParams = () => {
            const params = new URLSearchParams();
            filters.forEach((field) => {
                const value = String(field.value || "").trim();
                if (value) params.set(field.dataset.financeFilter, value);
            });
            return params;
        };

        const activateTab = (name) => {
            root.querySelectorAll("[data-finance-tab]").forEach((button) => {
                button.classList.toggle("is-active", button.dataset.financeTab === name);
            });
            root.querySelectorAll("[data-finance-panel]").forEach((panel) => {
                panel.hidden = panel.dataset.financePanel !== name;
            });
        };

        root.querySelectorAll("[data-finance-tab]").forEach((button) => {
            button.addEventListener("click", () => activateTab(button.dataset.financeTab));
        });

        activateTab(root.dataset.activeSubtab || "movements");

        const renderBranches = (branches) => {
            root.querySelectorAll('[data-finance-filter="branch_id"], [data-finance-branch-form]').forEach((select) => {
                const current = select.value;
                const first = select.dataset.financeFilter ? "Todas" : "General";
                select.innerHTML = `<option value="">${first}</option>`;

                branches.forEach((branch) => {
                    const option = document.createElement("option");
                    option.value = branch.id;
                    option.textContent = `${branch.branch_name}${branch.branch_code ? ` · ${branch.branch_code}` : ""}`;
                    select.appendChild(option);
                });

                select.value = current;
            });
        };

        const renderSummary = (summary) => {
            Object.entries(summary).forEach(([key, value]) => {
                const node = root.querySelector(`[data-finance-kpi="${key}"]`);
                if (node) node.textContent = money.format(Number(value || 0));
            });
        };

        const renderMovements = (rows) => {
            const tbody = root.querySelector("[data-finance-movements]");
            if (!rows.length) {
                tbody.innerHTML = '<tr><td colspan="8" class="commerce-finance__empty">No hay movimientos para los filtros seleccionados.</td></tr>';
                return;
            }

            tbody.innerHTML = rows.map((row) => `
                <tr>
                    <td>${escapeHtml(formatDate(row.occurred_at))}</td>
                    <td><strong>${escapeHtml(row.concept)}</strong><br><small>${escapeHtml(row.type)}</small></td>
                    <td>${escapeHtml(row.branch_id || "General")}</td>
                    <td>${money.format(Number(row.gross_amount || 0))}</td>
                    <td>${money.format(Number(row.commission_amount || 0))}</td>
                    <td>${money.format(Number(row.delivery_amount || 0))}</td>
                    <td><strong>${money.format(Number(row.net_amount || 0))}</strong></td>
                    <td><span class="commerce-finance__status">${escapeHtml(row.status)}</span></td>
                </tr>
            `).join("");
        };

        const renderSettlements = (rows) => {
            const target = root.querySelector("[data-finance-settlements]");
            target.innerHTML = rows.length ? rows.map((row) => `
                <article class="commerce-finance__card">
                    <div><strong>${escapeHtml(row.folio)}</strong><small>${escapeHtml(row.period_start)} a ${escapeHtml(row.period_end)}</small></div>
                    <div><span>Pedidos</span><strong>${Number(row.orders_count || 0)}</strong></div>
                    <div><span>Bruto</span><strong>${money.format(Number(row.gross_amount || 0))}</strong></div>
                    <div><span>Comisión</span><strong>${money.format(Number(row.commission_amount || 0))}</strong></div>
                    <div><span>Neto</span><strong>${money.format(Number(row.net_amount || 0))}</strong><small>${escapeHtml(row.status)}</small></div>
                </article>
            `).join("") : '<div class="commerce-finance__empty">Todavía no hay liquidaciones.</div>';
        };

        const renderInvoices = (rows) => {
            const target = root.querySelector("[data-finance-invoices]");
            target.innerHTML = rows.length ? rows.map((row) => `
                <tr>
                    <td>${escapeHtml(formatDate(row.created_at))}</td>
                    <td>${escapeHtml(row.invoice_type)}</td>
                    <td>${escapeHtml([row.series, row.folio].filter(Boolean).join("-") || "Pendiente")}</td>
                    <td>${escapeHtml(row.uuid || "Sin timbrar")}</td>
                    <td>${money.format(Number(row.total || 0))}</td>
                    <td><span class="commerce-finance__status">${escapeHtml(row.status)}</span></td>
                </tr>
            `).join("") : '<tr><td colspan="6" class="commerce-finance__empty">Todavía no hay comprobantes fiscales.</td></tr>';
        };

        const renderSeries = (rows) => {
            const target = root.querySelector("[data-finance-series]");
            target.innerHTML = rows.length ? rows.map((row) => `
                <article class="commerce-finance__card commerce-finance__card--series">
                    <div><strong>Serie ${escapeHtml(row.series)}</strong><small>CFDI ${escapeHtml(row.cfdi_type)} · ${escapeHtml(row.environment)}</small></div>
                    <div><span>Folio inicial</span><strong>${Number(row.initial_folio || 0)}</strong></div>
                    <div><span>Folio actual</span><strong>${Number(row.current_folio || 0)}</strong></div>
                    <div><span>Principal</span><strong>${row.is_default ? "Sí" : "No"}</strong></div>
                    <div><span>Estatus</span><strong>${row.is_active ? "Activa" : "Inactiva"}</strong></div>
                </article>
            `).join("") : '<div class="commerce-finance__empty">No hay series configuradas.</div>';
        };

        const postAction = async (url) => {
            const response = await fetch(url, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrf,
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                },
            });

            if (!response.ok) throw new Error("No se pudo completar la acción.");
            window.location.reload();
        };

        const renderBankAccounts = (rows) => {
            const target = root.querySelector("[data-finance-bank-accounts]");
            target.innerHTML = rows.length ? rows.map((row) => `
                <article class="commerce-finance__card commerce-finance__card--bank">
                    <div><strong>${escapeHtml(row.bank_name)}</strong><small>${escapeHtml(row.account_holder)}</small></div>
                    <div><span>CLABE</span><strong>${escapeHtml(row.masked_clabe)}</strong></div>
                    <div><span>Cuenta</span><strong>${escapeHtml(row.masked_account)}</strong></div>
                    <div><span>Principal</span><strong>${row.is_primary ? "Sí" : "No"}</strong></div>
                    <div><span>Estatus</span><strong>${escapeHtml(row.status)} · ${row.is_active ? "Activa" : "Inactiva"}</strong></div>
                    <div class="commerce-finance__card-actions">
                        ${row.is_primary ? "" : `<button type="button" data-bank-primary="${row.id}">Hacer principal</button>`}
                        <button type="button" data-bank-toggle="${row.id}">${row.is_active ? "Desactivar" : "Activar"}</button>
                    </div>
                </article>
            `).join("") : '<div class="commerce-finance__empty">No hay cuentas bancarias registradas.</div>';

            target.querySelectorAll("[data-bank-primary]").forEach((button) => {
                button.addEventListener("click", () => {
                    const url = root.dataset.bankPrimaryUrl.replace("__ID__", button.dataset.bankPrimary);
                    postAction(url).catch((error) => alert(error.message));
                });
            });

            target.querySelectorAll("[data-bank-toggle]").forEach((button) => {
                button.addEventListener("click", () => {
                    const url = root.dataset.bankToggleUrl.replace("__ID__", button.dataset.bankToggle);
                    postAction(url).catch((error) => alert(error.message));
                });
            });
        };

        const renderDisputes = (rows) => {
            const target = root.querySelector("[data-finance-disputes]");
            target.innerHTML = rows.length ? rows.map((row) => `
                <article class="commerce-finance__card">
                    <div><strong>${escapeHtml(row.folio)}</strong><small>${escapeHtml(row.subject)}</small></div>
                    <div><span>Tipo</span><strong>${escapeHtml(row.type)}</strong></div>
                    <div><span>Prioridad</span><strong>${escapeHtml(row.priority || "normal")}</strong></div>
                    <div><span>Monto</span><strong>${money.format(Number(row.claimed_amount || 0))}</strong></div>
                    <div><span>Fecha límite</span><strong>${escapeHtml(formatDate(row.due_at))}</strong></div>
                    <div><span>Estatus</span><strong>${escapeHtml(row.status)}</strong></div>
                </article>
            `).join("") : '<div class="commerce-finance__empty">No hay aclaraciones abiertas.</div>';
        };

        const fillTaxProfile = (profile) => {
            if (!profile) return;
            const form = root.querySelector('[data-finance-panel="tax"] form');
            [
                "person_type", "rfc", "legal_name", "tax_regime", "postal_code", "cfdi_use",
                "tax_email", "fiscal_street", "fiscal_number", "fiscal_colony",
                "fiscal_city", "fiscal_state", "environment",
            ].forEach((name) => {
                if (form?.elements[name]) form.elements[name].value = profile[name] || "";
            });

            const status = root.querySelector("[data-finance-tax-status]");
            if (status) status.textContent = profile.status || "incomplete";
        };

        const load = async () => {
            root.classList.add("is-loading");

            try {
                const response = await fetch(`${endpoint}?${queryParams().toString()}`, {
                    headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" },
                });
                const payload = await response.json();

                if (!response.ok || !payload.ok) throw new Error(payload.message || "No se pudo cargar Finanzas.");

                renderBranches(payload.branches || []);
                renderSummary(payload.summary || {});
                renderMovements(payload.movements || []);
                renderSettlements(payload.settlements || []);
                renderInvoices(payload.invoices || []);
                renderSeries(payload.invoice_series || []);
                renderBankAccounts(payload.bank_accounts || []);
                renderDisputes(payload.disputes || []);
                fillTaxProfile(payload.tax_profile);
            } catch (error) {
                console.error("PETPAY Finanzas", error);
                root.querySelector("[data-finance-movements]").innerHTML =
                    '<tr><td colspan="8" class="commerce-finance__empty">No se pudo cargar la información financiera.</td></tr>';
            } finally {
                root.classList.remove("is-loading");
            }
        };

        const bankSelect = root.querySelector("[data-bank-select]");
        const bankName = root.querySelector("[data-bank-name]");

        const syncBankName = () => {
            if (bankName && bankSelect) {
                bankName.value = bankSelect.selectedOptions[0]?.dataset.name || "";
            }
        };

        bankSelect?.addEventListener("change", syncBankName);
        syncBankName();

        root.querySelector("[data-finance-apply]")?.addEventListener("click", load);
        root.querySelector("[data-finance-refresh]")?.addEventListener("click", load);
        root.querySelector("[data-finance-export]")?.addEventListener("click", () => {
            window.location.href = `${exportUrl}?${queryParams().toString()}`;
        });

        load();
    };

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init, { once: true });
    } else {
        init();
    }
})();
