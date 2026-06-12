import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = { message: String, event: String, title: String };

    submit(e) {
        e.preventDefault();

        const dialog = document.getElementById('confirm-dialog');
        if (!dialog) {
            if (confirm(this.hasMessageValue ? this.messageValue : 'Are you sure?')) {
                this._doFetch();
            }
            return;
        }

        const titleEl = document.getElementById('confirm-dialog-title');
        const msgEl   = document.getElementById('confirm-dialog-message');
        const confirmBtn = document.getElementById('confirm-dialog-confirm');
        const cancelBtn  = document.getElementById('confirm-dialog-cancel');

        if (titleEl) titleEl.textContent = this.hasTitleValue ? this.titleValue : 'Are you sure?';
        if (msgEl)   msgEl.textContent   = this.hasMessageValue ? this.messageValue : '';

        dialog.showModal();

        const cleanup = () => {
            confirmBtn.removeEventListener('click', onConfirm);
            cancelBtn.removeEventListener('click', onCancel);
            dialog.removeEventListener('click', onBackdrop);
        };

        const onConfirm = () => { cleanup(); dialog.close(); this._doFetch(); };
        const onCancel  = () => { cleanup(); dialog.close(); };
        const onBackdrop = (e) => { if (e.target === dialog) { cleanup(); dialog.close(); } };

        confirmBtn.addEventListener('click', onConfirm, { once: true });
        cancelBtn.addEventListener('click', onCancel, { once: true });
        dialog.addEventListener('click', onBackdrop);
    }

    _doFetch() {
        fetch(this.element.action, {
            method: 'POST',
            body: new FormData(this.element),
        }).then(res => {
            const flashHeader = res.headers.get('X-Flash-Messages');
            if (flashHeader) {
                try {
                    const flashes = JSON.parse(flashHeader);
                    for (const [type, messages] of Object.entries(flashes)) {
                        if (Array.isArray(messages)) {
                            messages.forEach(msg =>
                                document.dispatchEvent(new CustomEvent('flash:show', { detail: { type, message: msg } }))
                            );
                        }
                    }
                } catch {}
            }
            if (res.ok && this.hasEventValue) {
                document.dispatchEvent(new CustomEvent(this.eventValue));
            }
        }).catch(() => {});
    }
}
