import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['container', 'form', 'submit', 'error', 'loading'];
    static values  = { pubkey: String, sessionUrl: String, returnUrl: String };

    async connect() {
        try {
            await this._init();
        } catch {
            this._showError('Could not initialize payment form. Please refresh.');
        }
    }

    async _init() {
        const res = await fetch(this.sessionUrlValue, { method: 'POST' });

        if (!res.ok) {
            const data = await res.json().catch(() => ({}));
            throw new Error(data.error ?? 'Session creation failed');
        }

        const { clientSecret } = await res.json();

        this.stripe   = Stripe(this.pubkeyValue);
        this.elements = this.stripe.elements({
            clientSecret,
            appearance: {
                theme: 'night',
                variables: {
                    colorPrimary:          '#c6c6c7',
                    colorBackground:       '#19191d',
                    colorText:             '#e7e4ec',
                    colorTextSecondary:    '#acaab1',
                    colorDanger:           '#ec7c8a',
                    fontFamily:            '"Inter", ui-sans-serif, system-ui, sans-serif',
                    spacingUnit:           '4px',
                    borderRadius:          '0px',
                },
                rules: {
                    '.Input': {
                        backgroundColor: '#131316',
                        border:          '1px solid #47474e',
                        color:           '#e7e4ec',
                    },
                    '.Input:focus': {
                        border:     '1px solid #c6c6c7',
                        boxShadow:  '0 0 0 3px rgba(198, 198, 199, 0.1)',
                    },
                    '.Label': {
                        color:          '#acaab1',
                        fontSize:       '11px',
                        fontWeight:     '600',
                        letterSpacing:  '0.06em',
                        textTransform:  'uppercase',
                    },
                    '.Tab': {
                        backgroundColor: '#19191d',
                        border:          '1px solid #47474e',
                        color:           '#acaab1',
                    },
                    '.Tab:hover': { backgroundColor: '#1f1f24', color: '#e7e4ec' },
                    '.Tab--selected': {
                        backgroundColor: '#1f1f24',
                        border:          '1px solid #c6c6c7',
                        color:           '#e7e4ec',
                    },
                    '.TabIcon--selected': { fill: '#e7e4ec' },
                    '.TabLabel--selected': { color: '#e7e4ec' },
                },
            },
        });

        const paymentEl = this.elements.create('payment');
        paymentEl.mount(this.containerTarget);

        if (this.hasLoadingTarget) {
            this.loadingTarget.remove();
        }
    }

    async submit(e) {
        e.preventDefault();

        if (this.hasSubmitTarget) {
            this.submitTarget.disabled    = true;
            this.submitTarget.textContent = 'Processing…';
        }

        const { error } = await this.stripe.confirmPayment({
            elements:        this.elements,
            confirmParams:   { return_url: this.returnUrlValue },
        });

        if (error) {
            this._showError(error.message ?? 'Payment failed.');

            if (this.hasSubmitTarget) {
                this.submitTarget.disabled    = false;
                this.submitTarget.textContent = 'Pay now';
            }
        }
    }

    _showError(msg) {
        if (!this.hasErrorTarget) return;
        this.errorTarget.textContent = msg;
        this.errorTarget.hidden      = false;
    }
}
