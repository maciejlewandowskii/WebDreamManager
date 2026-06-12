import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this._lastFrameUrl = null;

        this._onFrameRender = (e) => {
            if (e.target.id === 'modal' && e.target.closest('#app-modal')) {
                this._lastFrameUrl = e.detail?.fetchResponse?.response?.url ?? null;
            }
        };

        this._onFrameLoad = (e) => {
            if (e.target.id === 'modal' && e.target.closest('#app-modal')) {
                const hasContent = e.target.innerHTML.trim().length > 0;
                if (hasContent && !this.element.open) {
                    this.element.showModal();
                } else if (!hasContent && this.element.open) {
                    const url = this._lastFrameUrl;
                    this._lastFrameUrl = null;
                    this._closeImmediate();
                    if (url && window.Turbo) {
                        window.Turbo.visit(url);
                    }
                }
            }
        };

        this._onLinkClick = (e) => {
            const link = e.target.closest('a[data-turbo-frame="modal"]');
            if (link) {
                const titleEl = document.getElementById('modal-title');
                if (titleEl) titleEl.textContent = link.dataset.modalTitle ?? '';
            }
        };

        document.addEventListener('turbo:frame-render', this._onFrameRender);
        document.addEventListener('turbo:frame-load', this._onFrameLoad);
        document.addEventListener('click', this._onLinkClick);

        this.element.addEventListener('click', (e) => {
            if (e.target === this.element) this.close();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.element.open) {
                e.preventDefault();
                this.close();
            }
        });

        document.addEventListener('turbo:before-visit', () => {
            if (this.element.open) this._closeImmediate();
        });
    }

    disconnect() {
        document.removeEventListener('turbo:frame-render', this._onFrameRender);
        document.removeEventListener('turbo:frame-load', this._onFrameLoad);
        document.removeEventListener('click', this._onLinkClick);
    }

    close() {
        this.element.classList.add('closing');
        this.element.addEventListener('animationend', () => {
            this.element.classList.remove('closing');
            this._closeImmediate();
        }, { once: true });
    }

    _closeImmediate() {
        this.element.close();
        const frame = document.getElementById('modal');
        if (frame) frame.innerHTML = '';
        const titleEl = document.getElementById('modal-title');
        if (titleEl) titleEl.textContent = '';
    }
}
