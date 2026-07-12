(() => {
    const show = (element) => {
        if (!element) return;
        element.hidden = false;
        document.body.style.overflow = 'hidden';
    };

    const hide = (element) => {
        if (!element) return;
        element.hidden = true;
        document.body.style.overflow = '';
    };

    const monetizeModal = document.querySelector('[data-monetize-modal]');
    const contractModal = document.querySelector('[data-contract-modal]');

    document.querySelector('[data-monetize-open-form]')?.addEventListener('click', () => show(monetizeModal));
    document.querySelector('[data-contract-open-form]')?.addEventListener('click', () => show(contractModal));

    document.querySelectorAll('[data-monetize-close-form]').forEach((button) => {
        button.addEventListener('click', () => hide(monetizeModal));
    });

    document.querySelectorAll('[data-contract-close-form]').forEach((button) => {
        button.addEventListener('click', () => hide(contractModal));
    });

    document.querySelectorAll('[data-monetize-preset]').forEach((button) => {
        button.addEventListener('click', () => {
            const field = document.querySelector('[data-monetize-type-field]');
            if (field) field.value = button.dataset.monetizePreset || 'discount';
            show(monetizeModal);
        });
    });

    [monetizeModal, contractModal].forEach((modal) => {
        modal?.addEventListener('click', (event) => {
            if (event.target === modal) hide(modal);
        });
    });

    document.querySelectorAll('[data-contract-group-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const group = button.closest('[data-contract-group]');
            const body = group?.querySelector('.contract-group-body');
            if (body) body.hidden = !body.hidden;
        });
    });

    document.querySelectorAll('[data-contract-upload-open]').forEach((button) => {
        button.addEventListener('click', () => {
            const form = document.querySelector(`[data-contract-upload-form="${button.dataset.contractUploadOpen}"]`);
            if (form) form.hidden = !form.hidden;
        });
    });

    document.querySelectorAll('[data-contract-sign-open]').forEach((button) => {
        button.addEventListener('click', () => {
            const form = document.querySelector(`[data-contract-sign-form="${button.dataset.contractSignOpen}"]`);
            if (form) form.hidden = !form.hidden;
        });
    });

    const filterCampaigns = () => {
        const search = (document.querySelector('[data-monetize-search]')?.value || '').trim().toLowerCase();
        const status = document.querySelector('[data-monetize-status]')?.value || '';
        const type = document.querySelector('[data-monetize-type]')?.value || '';

        document.querySelectorAll('[data-monetize-card]').forEach((card) => {
            const visible =
                (!search || (card.dataset.search || '').includes(search)) &&
                (!status || card.dataset.status === status) &&
                (!type || card.dataset.type === type);

            card.hidden = !visible;
        });
    };

    ['[data-monetize-search]', '[data-monetize-status]', '[data-monetize-type]'].forEach((selector) => {
        document.querySelector(selector)?.addEventListener('input', filterCampaigns);
        document.querySelector(selector)?.addEventListener('change', filterCampaigns);
    });

    const filterContracts = () => {
        const search = (document.querySelector('[data-contract-search]')?.value || '').trim().toLowerCase();
        const year = document.querySelector('[data-contract-year]')?.value || '';
        const status = document.querySelector('[data-contract-status]')?.value || '';
        const type = document.querySelector('[data-contract-type]')?.value || '';
        const version = document.querySelector('[data-contract-version]')?.value || '';

        document.querySelectorAll('[data-contract-card]').forEach((card) => {
            const visible =
                (!search || (card.dataset.search || '').includes(search)) &&
                (!year || card.dataset.year === year) &&
                (!status || card.dataset.status === status) &&
                (!type || card.dataset.type === type) &&
                (!version || card.dataset.version === version);

            card.hidden = !visible;
        });
    };

    ['[data-contract-search]', '[data-contract-year]', '[data-contract-status]', '[data-contract-type]', '[data-contract-version]'].forEach((selector) => {
        document.querySelector(selector)?.addEventListener('input', filterContracts);
        document.querySelector(selector)?.addEventListener('change', filterContracts);
    });

    document.querySelector('[data-contract-group-toggle]')?.click();
    const identityRoot = document.querySelector('[data-identity-root]');
    const identityTypeInputs = identityRoot?.querySelectorAll('input[name="person_type"]') || [];

    const identityText = {
        individual: {
            intro: 'Validaremos al titular que firma y opera por cuenta propia.',
            summary: 'Titular',
            businessTitle: 'Datos del titular',
            businessHelp: 'Captura los datos fiscales de la persona física.',
            legalName: 'Nombre legal completo',
            businessRfc: 'RFC del titular',
            address: 'Domicilio del titular',
            representativeTitle: 'Datos del titular firmante',
            representativeHelp: 'Estos datos deben coincidir con INE, CURP, RFC y e.firma.',
            personName: 'Nombre completo',
            personRfc: 'RFC del titular',
            documentsTitle: 'Documentos de persona física',
            documentsHelp: 'Sube identificación, constancia fiscal, domicilio, selfie y prueba de vida.',
            docs: {
                ine_front: 'INE frente',
                ine_back: 'INE reverso',
                proof_address: 'Comprobante de domicilio',
                tax_certificate: 'Constancia de situación fiscal',
                selfie: 'Selfie del titular',
                liveness: 'Prueba de vida',
            },
        },
        company: {
            intro: 'Validaremos a la empresa y las facultades de su representante legal.',
            summary: 'Representante legal',
            businessTitle: 'Datos de la persona moral',
            businessHelp: 'Captura la razón social, RFC y domicilio fiscal de la empresa.',
            legalName: 'Razón social',
            businessRfc: 'RFC de la empresa',
            address: 'Domicilio fiscal',
            representativeTitle: 'Datos del representante legal',
            representativeHelp: 'Los datos deben coincidir con INE, CURP, poder notarial y e.firma.',
            personName: 'Nombre del representante legal',
            personRfc: 'RFC del representante',
            documentsTitle: 'Documentos de persona moral',
            documentsHelp: 'Incluye documentos de la empresa, del representante y de sus facultades.',
            docs: {
                ine_front: 'INE frente del representante',
                ine_back: 'INE reverso del representante',
                proof_address: 'Comprobante de domicilio fiscal',
                tax_certificate: 'Constancia fiscal de la empresa',
                selfie: 'Selfie del representante',
                liveness: 'Prueba de vida del representante',
            },
        },
    };

    const setIdentityText = (selector, value) => {
        const element = identityRoot?.querySelector(selector);
        if (element) element.textContent = value;
    };

    const syncIdentityPersonType = () => {
        if (!identityRoot) return;

        const selected = identityRoot.querySelector('input[name="person_type"]:checked')?.value || 'individual';
        const company = selected === 'company';
        const copy = identityText[selected];

        identityRoot.dataset.personType = selected;

        identityRoot.querySelectorAll('[data-company-only-section], [data-company-only-field]').forEach((element) => {
            element.hidden = !company;
        });

        identityRoot.querySelectorAll('[data-individual-only-field]').forEach((element) => {
            element.hidden = company;
        });

        identityRoot.querySelectorAll('[data-company-required]').forEach((field) => {
            field.required = company;
            if (!company && field.type !== 'checkbox') {
                field.setCustomValidity('');
            }
        });

        identityRoot.querySelectorAll('[data-document-scope="company"]').forEach((card) => {
            card.hidden = !company;
            const file = card.querySelector('input[type="file"]');
            if (file) file.required = company;
        });

        setIdentityText('[data-identity-intro]', copy.intro);
        setIdentityText('[data-identity-summary-person-label]', copy.summary);
        setIdentityText('[data-identity-business-title]', copy.businessTitle);
        setIdentityText('[data-identity-business-help]', copy.businessHelp);
        setIdentityText('[data-identity-legal-name-label]', copy.legalName);
        setIdentityText('[data-identity-business-rfc-label]', copy.businessRfc);
        setIdentityText('[data-identity-address-label]', copy.address);
        setIdentityText('[data-identity-representative-title]', copy.representativeTitle);
        setIdentityText('[data-identity-representative-help]', copy.representativeHelp);
        setIdentityText('[data-identity-person-name-label]', copy.personName);
        setIdentityText('[data-identity-person-rfc-label]', copy.personRfc);
        setIdentityText('[data-identity-documents-title]', copy.documentsTitle);
        setIdentityText('[data-identity-documents-help]', copy.documentsHelp);

        Object.entries(copy.docs).forEach(([key, label]) => {
            setIdentityText(`[data-dynamic-document-label="${key}"]`, label);
        });

        identityRoot.querySelectorAll('.identity-person-option').forEach((option) => {
            const input = option.querySelector('input[name="person_type"]');
            option.classList.toggle('is-selected', input?.checked === true);
        });
    };

    identityTypeInputs.forEach((input) => {
        input.addEventListener('change', syncIdentityPersonType);
    });

    syncIdentityPersonType();

})();