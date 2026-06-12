import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['nameDialog', 'nameInput'];
    static values = {
        registerOptionsUrl: String,
        registerUrl: String,
        loginOptionsUrl: String,
        loginUrl: String,
    };

    connect() {
        if (this.hasNameDialogTarget) {
            // Close on backdrop click
            this.nameDialogTarget.addEventListener('click', (e) => {
                if (e.target === this.nameDialogTarget) this.cancelName();
            });
            // Enter to confirm, Escape to cancel
            this.nameInputTarget.addEventListener('keydown', (e) => {
                if (e.key === 'Enter')  { e.preventDefault(); this.confirmName(); }
                if (e.key === 'Escape') { e.preventDefault(); this.cancelName(); }
            });
        }
    }

    async register() {
        try {
            const name = await this._promptName();
            if (name === null) return;

            const optRes  = await fetch(this.registerOptionsUrlValue);
            const options = await optRes.json();

            const pubKey = options.publicKey;
            pubKey.challenge = this._base64urlDecode(pubKey.challenge);
            pubKey.user.id   = this._base64urlDecode(pubKey.user.id);
            if (pubKey.excludeCredentials) {
                pubKey.excludeCredentials = pubKey.excludeCredentials.map(c => ({
                    ...c,
                    id: this._base64urlDecode(c.id),
                }));
            }

            const credential = await navigator.credentials.create({ publicKey: pubKey });

            const payload = {
                name,
                id:   credential.id,
                type: credential.type,
                response: {
                    clientDataJSON:    this._base64encode(credential.response.clientDataJSON),
                    attestationObject: this._base64encode(credential.response.attestationObject),
                },
            };

            const saveRes = await fetch(this.registerUrlValue, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify(payload),
            });

            const result = await saveRes.json();
            if (result.ok) {
                window.location.reload();
            } else {
                this._showError(result.error ?? 'Registration failed.');
            }
        } catch (err) {
            if (err.name !== 'NotAllowedError') {
                this._showError(err.message || 'An unexpected error occurred.');
            }
        }
    }

    async login() {
        if (window.location.protocol === 'http:') {
            window.location.href = 'https://' + window.location.host + window.location.pathname + window.location.hash;
            return;
        }
        try {
            const optRes  = await fetch(this.loginOptionsUrlValue);
            const options = await optRes.json();

            const pubKey = options.publicKey;
            pubKey.challenge = this._base64urlDecode(pubKey.challenge);
            if (pubKey.allowCredentials) {
                pubKey.allowCredentials = pubKey.allowCredentials.map(c => ({
                    ...c, id: this._base64urlDecode(c.id),
                }));
            }

            const assertion = await navigator.credentials.get({ publicKey: pubKey });

            const payload = {
                id:   assertion.id,
                type: assertion.type,
                response: {
                    clientDataJSON:    this._base64encode(assertion.response.clientDataJSON),
                    authenticatorData: this._base64encode(assertion.response.authenticatorData),
                    signature:         this._base64encode(assertion.response.signature),
                    userHandle: assertion.response.userHandle
                        ? this._base64encode(assertion.response.userHandle)
                        : null,
                },
            };

            const saveRes = await fetch(this.loginUrlValue, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify(payload),
            });

            const result = await saveRes.json();
            if (result.ok) {
                window.location.href = result.redirect ?? '/';
            } else {
                this._showError(result.error ?? 'Authentication failed.');
            }
        } catch (err) {
            if (err.name !== 'NotAllowedError') {
                this._showError(err.message || 'An unexpected error occurred.');
            }
        }
    }

    confirmName() {
        const name = this.nameInputTarget.value.trim();
        this.nameDialogTarget.close();
        if (this._nameResolve) {
            this._nameResolve(name || 'Passkey');
            this._nameResolve = null;
        }
    }

    cancelName() {
        this.nameDialogTarget.close();
        if (this._nameResolve) {
            this._nameResolve(null);
            this._nameResolve = null;
        }
    }

    _promptName() {
        return new Promise((resolve) => {
            this._nameResolve = resolve;
            this.nameInputTarget.value = '';
            this.nameDialogTarget.showModal();
            setTimeout(() => this.nameInputTarget.focus(), 50);
        });
    }

    _showError(message) {
        document.dispatchEvent(new CustomEvent('flash:show', {
            detail: { type: 'error', message },
            bubbles: true,
        }));
    }

    _base64urlDecode(str) {
        const pad = str.length % 4 === 0 ? '' : '='.repeat(4 - (str.length % 4));
        const b64 = (str + pad).replace(/-/g, '+').replace(/_/g, '/');
        const bin = atob(b64);
        return Uint8Array.from(bin, c => c.charCodeAt(0));
    }

    _base64encode(buffer) {
        return btoa(String.fromCharCode(...new Uint8Array(buffer)));
    }
}
