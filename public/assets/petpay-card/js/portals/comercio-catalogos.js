(() => {
    "use strict";

    const init = () => {
        const form = document.getElementById("commerceCatalogProductForm");

        if (!form) {
            return;
        }

        const search = document.getElementById("commerceCatalogSearch");
        const categoryFilter = document.getElementById("commerceCatalogCategoryFilter");
        const statusFilter = document.getElementById("commerceCatalogStatusFilter");
        const cards = Array.from(document.querySelectorAll("[data-catalog-product-card]"));
        const resetButton = document.getElementById("commerceCatalogResetButton");
        const methodInput = document.getElementById("commerceCatalogMethod");
        const submitButton = document.getElementById("commerceCatalogSubmitButton");
        const variantsContainer = document.getElementById("catalogVariantsContainer");
        const addVariantButton = document.getElementById("catalogAddVariantButton");
        const imageInput = document.getElementById("catalogImageInput");
        const imagePreview = document.getElementById("catalogImagePreview");
        const priceInput = document.getElementById("catalogPrice");
        const costInput = document.getElementById("catalogCost");
        const saleInput = document.getElementById("catalogSalePrice");
        const marginValue = document.getElementById("catalogMarginValue");
        const profitValue = document.getElementById("catalogProfitValue");
        const destinationLatitude = document.getElementById("catalogDestinationLatitude");
        const destinationLongitude = document.getElementById("catalogDestinationLongitude");
        const destinationProduct = document.getElementById("catalogDestinationProduct");
        const destinationQuantity = document.getElementById("catalogDestinationQuantity");
        const destinationSearchButton = document.getElementById("catalogDestinationSearchButton");
        const destinationResults = document.getElementById("catalogDestinationResults");
        const branchRows = Array.from(document.querySelectorAll("[data-catalog-branch-row]"));

        let variantIndex = 0;

        const applyFilters = () => {
            const needle = String(search?.value || "").toLowerCase().trim();
            const category = String(categoryFilter?.value || "");
            const status = String(statusFilter?.value || "");

            cards.forEach((card) => {
                const matchesSearch = !needle || String(card.dataset.search || "").includes(needle);
                const matchesCategory = !category || String(card.dataset.category || "") === category;
                const matchesStatus = !status || String(card.dataset.status || "") === status;
                card.hidden = !(matchesSearch && matchesCategory && matchesStatus);
            });
        };

        [search, categoryFilter, statusFilter].forEach((field) => {
            field?.addEventListener("input", applyFilters);
            field?.addEventListener("change", applyFilters);
        });

        document.querySelectorAll("[data-catalog-subtab]").forEach((button) => {
            button.addEventListener("click", () => {
                const name = button.dataset.catalogSubtab;

                document.querySelectorAll("[data-catalog-subtab]").forEach((item) => {
                    item.classList.toggle("is-active", item === button);
                });

                document.querySelectorAll("[data-catalog-subpanel]").forEach((panel) => {
                    panel.hidden = panel.dataset.catalogSubpanel !== name;
                });
            });
        });

        document.querySelectorAll("[data-catalog-toggle]").forEach((button) => {
            button.addEventListener("click", () => {
                button.closest(".commerce-catalog__block--collapsible")?.classList.toggle("is-open");
            });
        });

        document.querySelectorAll("[data-catalog-open-section]").forEach((button) => {
            button.addEventListener("click", () => {
                const target = document.querySelector(`[data-catalog-section="${button.dataset.catalogOpenSection}"]`);
                target?.classList.add("is-open");
                target?.scrollIntoView({ behavior: "smooth", block: "start" });
            });
        });

        const syncBranchAssignmentState = (row) => {
            const assigned = row.querySelector("[data-branch-assigned]");
            const body = row.querySelector("[data-branch-assignment-body]");
            const enabled = Boolean(assigned?.checked);

            row.classList.toggle("is-assigned", enabled);

            if (body) {
                body.hidden = !enabled;
            }
        };

        branchRows.forEach((row) => {
            const assigned = row.querySelector("[data-branch-assigned]");

            assigned?.addEventListener("change", () => {
                syncBranchAssignmentState(row);
            });

            syncBranchAssignmentState(row);
        });

        const renderDestinationResults = (rows) => {
            if (!destinationResults) {
                return;
            }

            destinationResults.innerHTML = "";

            if (!Array.isArray(rows) || rows.length === 0) {
                destinationResults.innerHTML = `
                    <div class="commerce-catalog__empty">
                        No hay una sucursal con cobertura, disponibilidad y existencia para ese destino.
                    </div>
                `;
                return;
            }

            rows.forEach((row, index) => {
                const item = document.createElement("article");
                item.className = "commerce-catalog__destination-result";
                item.innerHTML = `
                    <div>
                        <strong>${index === 0 ? "Mejor opción · " : ""}${escapeAttribute(row.branch_name)}</strong>
                        <span>${escapeAttribute(row.product_name)} · ${escapeAttribute(row.sku)}</span>
                    </div>
                    <div><span>Distancia</span><strong>${Number(row.distance_km || 0).toFixed(2)} km</strong></div>
                    <div><span>Disponible</span><strong>${Number(row.available_stock || 0).toFixed(3)}</strong></div>
                    <div><span>Precio</span><strong>$${Number(row.price || 0).toFixed(2)}</strong></div>
                    <div><span>Preparación</span><strong>${Number(row.preparation_minutes || 0)} min</strong></div>
                `;
                destinationResults.appendChild(item);
            });
        };

        destinationSearchButton?.addEventListener("click", async () => {
            const latitude = Number(destinationLatitude?.value || "");
            const longitude = Number(destinationLongitude?.value || "");
            const quantity = Number(destinationQuantity?.value || 1);

            if (!Number.isFinite(latitude) || latitude < -90 || latitude > 90) {
                alert("Captura una latitud válida.");
                destinationLatitude?.focus();
                return;
            }

            if (!Number.isFinite(longitude) || longitude < -180 || longitude > 180) {
                alert("Captura una longitud válida.");
                destinationLongitude?.focus();
                return;
            }

            const url = destinationSearchButton.dataset.url;

            if (!url) {
                alert("No está configurada la ruta de disponibilidad.");
                return;
            }

            destinationSearchButton.disabled = true;
            destinationSearchButton.textContent = "Buscando...";

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";
                const response = await fetch(url, {
                    method: "POST",
                    headers: {
                        "Accept": "application/json",
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": token,
                    },
                    body: JSON.stringify({
                        latitude,
                        longitude,
                        product_id: destinationProduct?.value || null,
                        quantity,
                    }),
                });

                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message || "No se pudo consultar la disponibilidad.");
                }

                renderDestinationResults(payload.results || []);
            } catch (error) {
                console.error("PETPAY disponibilidad:", error);
                renderDestinationResults([]);
                alert(error.message || "No se pudo consultar la disponibilidad.");
            } finally {
                destinationSearchButton.disabled = false;
                destinationSearchButton.textContent = "Buscar sucursal cercana";
            }
        });

        const calculateMargin = () => {
            const cost = Number(costInput?.value || 0);
            const normal = Number(priceInput?.value || 0);
            const sale = Number(saleInput?.value || 0);
            const effective = sale > 0 ? sale : normal;
            const profit = effective - cost;
            const margin = effective > 0 ? (profit / effective) * 100 : 0;

            if (marginValue) {
                marginValue.textContent = `${margin.toFixed(2)}%`;
            }

            if (profitValue) {
                profitValue.textContent = `$${profit.toFixed(2)} utilidad`;
            }
        };

        [priceInput, costInput, saleInput].forEach((field) => {
            field?.addEventListener("input", calculateMargin);
        });

        imageInput?.addEventListener("change", () => {
            const file = imageInput.files?.[0];

            if (!file || !imagePreview) {
                return;
            }

            const reader = new FileReader();

            reader.addEventListener("load", () => {
                imagePreview.src = String(reader.result || "");
                imagePreview.hidden = false;
            });

            reader.readAsDataURL(file);
        });

        const escapeAttribute = (value) => {
            return String(value ?? "")
                .replaceAll("&", "&amp;")
                .replaceAll('"', "&quot;")
                .replaceAll("<", "&lt;")
                .replaceAll(">", "&gt;");
        };

        const addVariant = (data = {}) => {
            if (!variantsContainer) {
                return;
            }

            const index = variantIndex++;
            const row = document.createElement("div");

            row.className = "commerce-catalog__variant-row";
            row.innerHTML = `
                <label class="commerce-catalog__field">
                    <span>Nombre</span>
                    <input name="variants[${index}][name]" value="${escapeAttribute(data.name)}">
                </label>
                <label class="commerce-catalog__field">
                    <span>SKU</span>
                    <input name="variants[${index}][sku]" value="${escapeAttribute(data.sku)}">
                </label>
                <label class="commerce-catalog__field">
                    <span>Código</span>
                    <input name="variants[${index}][barcode]" value="${escapeAttribute(data.barcode)}">
                </label>
                <label class="commerce-catalog__field">
                    <span>Atributos</span>
                    <input name="variants[${index}][attributes]" value="${escapeAttribute(data.attributes)}" placeholder="5 kg, pollo">
                </label>
                <label class="commerce-catalog__field">
                    <span>Precio</span>
                    <input type="number" min="0" step="0.01" name="variants[${index}][price]" value="${escapeAttribute(data.price)}">
                </label>
                <label class="commerce-catalog__field">
                    <span>Oferta</span>
                    <input type="number" min="0" step="0.01" name="variants[${index}][sale_price]" value="${escapeAttribute(data.sale_price)}">
                </label>
                <button type="button" class="commerce-catalog__variant-remove" aria-label="Eliminar variante">×</button>
            `;

            row.querySelector(".commerce-catalog__variant-remove")
                ?.addEventListener("click", () => row.remove());

            variantsContainer.appendChild(row);
        };

        addVariantButton?.addEventListener("click", () => addVariant());

        const setField = (name, value) => {
            const field = form.elements.namedItem(name);

            if (!field) {
                return;
            }

            if (field instanceof RadioNodeList) {
                Array.from(field).forEach((item) => {
                    if (item.type === "checkbox") {
                        item.checked = Boolean(value);
                    }
                });

                return;
            }

            if (field.type === "checkbox") {
                field.checked = Boolean(value);
                return;
            }

            field.value = value ?? "";
            field.dispatchEvent(new Event("input", { bubbles: true }));
            field.dispatchEvent(new Event("change", { bubbles: true }));
        };

        const clearDynamic = () => {
            if (variantsContainer) {
                variantsContainer.innerHTML = "";
            }

            variantIndex = 0;

            if (imagePreview) {
                imagePreview.removeAttribute("src");
                imagePreview.hidden = true;
            }
        };

        const decodeProductPayload = (button) => {
            const encoded = String(button.dataset.productBase64 || "").trim();

            if (!encoded) {
                throw new Error("El producto no contiene datos de edición.");
            }

            const binary = atob(encoded);
            const bytes = Uint8Array.from(binary, (character) => character.charCodeAt(0));
            const json = new TextDecoder("utf-8").decode(bytes);

            return JSON.parse(json);
        };

        const resetForm = () => {
            form.reset();
            form.action = form.dataset.storeAction;
            form.method = "POST";

            if (methodInput) {
                methodInput.value = "POST";
            }

            if (submitButton) {
                submitButton.textContent = "Guardar producto";
            }

            if (resetButton) {
                resetButton.hidden = true;
            }

            clearDynamic();

            branchRows.forEach((row) => {
                const assigned = row.querySelector("[data-branch-assigned]");
                if (assigned) {
                    assigned.checked = false;
                }
                syncBranchAssignmentState(row);
            });

            calculateMargin();
        };

        resetButton?.addEventListener("click", resetForm);

        const editProduct = (button) => {
            let product;

            try {
                product = decodeProductPayload(button);
            } catch (error) {
                console.error("PETPAY catálogo: no se pudo leer el producto.", error);
                alert("No se pudieron cargar los datos del producto para editar.");
                return;
            }

            form.action = button.dataset.updateAction || form.dataset.storeAction;
            form.method = "POST";

            if (methodInput) {
                methodInput.value = "PUT";
            }

            [
                "item_type",
                "name",
                "sku",
                "barcode",
                "category_id",
                "brand_id",
                "supplier_name",
                "short_description",
                "description",
                "price",
                "cost",
                "sale_price",
                "sale_starts_at",
                "sale_ends_at",
                "unit",
                "status",
                "tags",
                "weight",
                "length",
                "width",
                "height",
            ].forEach((name) => setField(name, product[name]));

            setField("track_stock", product.track_stock);
            setField("is_visible", product.is_visible);

            clearDynamic();

            (product.variants || []).forEach((variant) => {
                addVariant(variant);
            });

            Object.entries(product.stocks || {}).forEach(([branchId, stock]) => {
                setField(`branch_assigned[${branchId}]`, stock.is_assigned);
                setField(`branch_stock[${branchId}]`, stock.stock);
                setField(`branch_reserved_stock[${branchId}]`, stock.reserved_stock);
                setField(`branch_minimum_stock[${branchId}]`, stock.minimum_stock);
                setField(`branch_price[${branchId}]`, stock.branch_price);
                setField(`branch_sale_price[${branchId}]`, stock.branch_sale_price);
                setField(`branch_sale_starts_at[${branchId}]`, stock.branch_sale_starts_at);
                setField(`branch_sale_ends_at[${branchId}]`, stock.branch_sale_ends_at);
                setField(`branch_max_purchase_quantity[${branchId}]`, stock.max_purchase_quantity);
                setField(`branch_available_from[${branchId}]`, stock.available_from);
                setField(`branch_available_to[${branchId}]`, stock.available_to);
                setField(`branch_fulfillment_priority[${branchId}]`, stock.fulfillment_priority || 100);
                setField(`branch_coverage_radius_km[${branchId}]`, stock.coverage_radius_km);
                setField(`branch_allow_delivery[${branchId}]`, stock.allow_delivery);
                setField(`branch_allow_pickup[${branchId}]`, stock.allow_pickup);
                setField(`branch_available[${branchId}]`, stock.is_available);

                const days = Array.isArray(stock.available_days)
                    ? stock.available_days.map(String)
                    : [];

                form.querySelectorAll(`input[name="branch_available_days[${branchId}][]"]`).forEach((input) => {
                    input.checked = days.includes(String(input.value));
                });

                const row = document.querySelector(`[data-catalog-branch-row="${branchId}"]`);
                if (row) {
                    syncBranchAssignmentState(row);
                }
            });

            calculateMargin();

            if (submitButton) {
                submitButton.textContent = "Actualizar producto";
            }

            if (resetButton) {
                resetButton.hidden = false;
            }

            const productsTab = document.querySelector('[data-catalog-subtab="products"]');

            if (productsTab && !productsTab.classList.contains("is-active")) {
                productsTab.click();
            }

            const section = document.querySelector('[data-catalog-section="productForm"]');

            section?.classList.add("is-open");
            section?.scrollIntoView({ behavior: "smooth", block: "start" });
        };

        document.addEventListener("click", (event) => {
            const button = event.target.closest("[data-catalog-edit-product]");

            if (!button) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            editProduct(button);
        }, true);

        form.addEventListener("submit", () => {
            form.method = "POST";

            if (methodInput && methodInput.value !== "PUT") {
                methodInput.value = "POST";
            }
        });

        calculateMargin();

        window.petpayCatalogEditProduct = editProduct;
    };

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init, { once: true });
    } else {
        init();
    }
})();