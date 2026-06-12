import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        initial: Object
    };

    connect() {
        // 1. Process initial flash messages (e.g. from page load)
        try {
            if (this.hasInitialValue && this.initialValue) {
                const initial = this.initialValue;
                if (typeof initial === 'object' && !Array.isArray(initial)) {
                    for (const [type, messages] of Object.entries(initial)) {
                        if (Array.isArray(messages)) {
                            messages.forEach(message => {
                                if (message) {
                                    this.showAlert(type, message);
                                }
                            });
                        }
                    }
                }
            }
        } catch (err) {
            console.error('Error displaying initial flash messages:', err);
        }

        // 2. Register Turbo request & response interception
        this._onBeforeFetchRequest = (e) => {
            try {
                if (e.detail && e.detail.fetchOptions) {
                    e.detail.fetchOptions.headers = e.detail.fetchOptions.headers || {};
                    e.detail.fetchOptions.headers['X-Turbo-Request'] = 'true';
                }
            } catch (err) {
                console.error('Error in turbo:before-fetch-request handler:', err);
            }
        };

        this._onBeforeFetchResponse = (e) => {
            try {
                const fetchResponse = e.detail?.fetchResponse;
                const response = fetchResponse?.response;
                if (response && response.headers && typeof response.headers.get === 'function') {
                    const flashHeader = response.headers.get('X-Flash-Messages');
                    if (flashHeader) {
                        const flashes = JSON.parse(flashHeader);
                        if (flashes && typeof flashes === 'object' && !Array.isArray(flashes)) {
                            for (const [type, messages] of Object.entries(flashes)) {
                                if (Array.isArray(messages)) {
                                    messages.forEach(message => {
                                        if (message) {
                                            this.showAlert(type, message);
                                        }
                                    });
                                }
                            }
                        }
                    }
                }
            } catch (err) {
                console.error('Error parsing X-Flash-Messages header:', err);
            }
        };

        this._onFlashShow = (e) => {
            const { type, message } = e.detail || {};
            if (type && message) this.showAlert(type, message);
        };

        document.addEventListener('turbo:before-fetch-request', this._onBeforeFetchRequest);
        document.addEventListener('turbo:before-fetch-response', this._onBeforeFetchResponse);
        document.addEventListener('flash:show', this._onFlashShow);
    }

    disconnect() {
        if (this._onBeforeFetchRequest) {
            document.removeEventListener('turbo:before-fetch-request', this._onBeforeFetchRequest);
        }
        if (this._onBeforeFetchResponse) {
            document.removeEventListener('turbo:before-fetch-response', this._onBeforeFetchResponse);
        }
        if (this._onFlashShow) {
            document.removeEventListener('flash:show', this._onFlashShow);
        }
    }

    showAlert(type, message) {
        try {
            const item = document.createElement('div');
            const variantClass = this._getVariantClass(type);
            item.className = `alert-item ${variantClass}`;

            const iconSvg = this._getIconSvg(type);
            const title = this._getTitle(type);

            item.innerHTML = `
                ${iconSvg}
                <div class="alert-content">
                    <h5 class="alert-title">${title}</h5>
                    <p class="alert-message">${this._escapeHtml(message)}</p>
                </div>
                <button class="alert-close" aria-label="Close">&times;</button>
            `;

            // Add to container
            this.element.appendChild(item);

            const closeBtn = item.querySelector('.alert-close');
            
            let timeoutId;
            const removeAlert = () => {
                if (timeoutId) clearTimeout(timeoutId);
                item.classList.add('removing');
                item.addEventListener('transitionend', () => {
                    item.remove();
                }, { once: true });
            };

            if (closeBtn) {
                closeBtn.addEventListener('click', removeAlert);
            }

            item.addEventListener('mouseenter', () => {
                if (timeoutId) { clearTimeout(timeoutId); timeoutId = null; }
            });
            item.addEventListener('mouseleave', () => {
                timeoutId = setTimeout(removeAlert, 3000);
            });

            // Auto-remove after 5 seconds
            timeoutId = setTimeout(removeAlert, 5000);
        } catch (err) {
            console.error('Error displaying alert:', err);
        }
    }

    _getVariantClass(type) {
        if (typeof type !== 'string') return 'alert-info';
        const t = type.toLowerCase();
        if (t === 'success') return 'alert-success';
        if (t === 'error' || t === 'danger') return 'alert-error';
        if (t === 'warning') return 'alert-warning';
        return 'alert-info';
    }

    _getTitle(type) {
        if (typeof type !== 'string') return 'Info';
        const t = type.toLowerCase();
        if (t === 'success') return 'Success';
        if (t === 'error' || t === 'danger') return 'Error';
        if (t === 'warning') return 'Warning';
        return 'Info';
    }

    _getIconSvg(type) {
        if (typeof type !== 'string') type = 'info';
        const t = type.toLowerCase();
        if (t === 'success') {
            return `<svg class="alert-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
        }
        if (t === 'error' || t === 'danger' || t === 'warning') {
            return `<svg class="alert-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>`;
        }
        return `<svg class="alert-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
    }

    _escapeHtml(str) {
        if (typeof str !== 'string') {
            return String(str || '');
        }
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
}
