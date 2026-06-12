import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    connect() {
        this.boundUpdateUrl = this.updateUrl.bind(this);
        this.element.addEventListener('click', this.boundUpdateUrl, true);
    }

    disconnect() {
        this.element.removeEventListener('click', this.boundUpdateUrl, true);
    }

    updateUrl(e) {
        let dateInput = document.querySelector('input[data-model="currentDate"]');
        if (this.element.href) {
            let url = new URL(this.element.href, window.location.origin);
            if (dateInput && dateInput.value) {
                url.searchParams.set('date', dateInput.value);
            } else {
                url.searchParams.delete('date');
            }
            this.element.href = url.toString();
        }
    }
}
