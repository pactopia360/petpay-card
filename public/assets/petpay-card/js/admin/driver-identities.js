(() => {
    'use strict';

    const root = document.querySelector('[data-driver-identity-review]');

    if (!root) {
        return;
    }

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    const escapeHtml = (value) => {
        const element = document.createElement('div');
        element.textContent = String(value ?? '');
        return element.innerHTML;
    };

    root
        .querySelectorAll('[data-admin-analyze-document]')
        .forEach((button) => {
            button.addEventListener('click', async () => {
                const card = button.closest('[data-admin-document-card]');
                const url = button.dataset.analyzeUrl;
                const panel = card?.querySelector(
                    '[data-admin-analysis-panel]'
                );
                const status = card?.querySelector(
                    '[data-admin-analysis-status]'
                );
                const message = card?.querySelector(
                    '[data-admin-analysis-message]'
                );

                if (!card || !url || !csrfToken) {
                    return;
                }

                const originalText = button.textContent;

                button.disabled = true;
                button.textContent = 'Analizando...';

                if (status) {
                    status.className =
                        'driver-review-ai-status is-processing';
                    status.textContent = 'Procesando';
                }

                if (message) {
                    message.textContent =
                        'La IA está revisando el documento.';
                }

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });

                    const payload = await response.json();

                    if (!response.ok || !payload.ok) {
                        throw new Error(
                            payload.message
                            || 'No fue posible analizar el documento.'
                        );
                    }

                    const documentData = payload.document || {};

                    if (status) {
                        status.className =
                            `driver-review-ai-status is-${documentData.analysis_status || 'completed'}`;
                        status.textContent =
                            documentData.analysis_status === 'manual_review'
                                ? 'Revisión manual'
                                : 'Analizado';
                    }

                    if (message) {
                        message.textContent =
                            payload.message
                            || 'Documento analizado correctamente.';
                    }

                    const confidence = panel?.querySelector(
                        '[data-admin-analysis-confidence]'
                    );

                    const quality = panel?.querySelector(
                        '[data-admin-analysis-quality]'
                    );

                    if (confidence) {
                        confidence.textContent =
                            documentData.analysis_confidence !== null
                            && documentData.analysis_confidence !== undefined
                                ? `${Number(documentData.analysis_confidence).toFixed(1)} %`
                                : '—';
                    }

                    if (quality) {
                        quality.textContent =
                            documentData.quality_score !== null
                            && documentData.quality_score !== undefined
                                ? `${Number(documentData.quality_score).toFixed(1)} %`
                                : '—';
                    }

                    const dataContainer = panel?.querySelector(
                        '[data-admin-analysis-data]'
                    );

                    if (dataContainer) {
                        const extracted = documentData.extracted_data || {};

                        dataContainer.innerHTML = Object
                            .entries(extracted)
                            .filter(([, value]) => {
                                return value !== null
                                    && value !== ''
                                    && typeof value !== 'object';
                            })
                            .map(([key, value]) => {
                                return `
                                    <span>
                                        <small>${escapeHtml(
                                            key.replaceAll('_', ' ')
                                        )}</small>
                                        <strong>${escapeHtml(value)}</strong>
                                    </span>
                                `;
                            })
                            .join('');
                    }
                } catch (error) {
                    if (status) {
                        status.className =
                            'driver-review-ai-status is-failed';
                        status.textContent = 'Error';
                    }

                    if (message) {
                        message.textContent =
                            error.message
                            || 'No fue posible analizar el documento.';
                    }
                } finally {
                    button.disabled = false;
                    button.textContent = originalText;
                }
            });
        });
})();
