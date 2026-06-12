import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['tab', 'panel'];

    connect() {
        const initial = location.hash.replace('#', '') || this.tabTargets[0]?.dataset.tab;
        this._show(initial);
    }

    select(e) {
        e.preventDefault();
        const tab = e.currentTarget.dataset.tab;
        history.replaceState(null, '', '#' + tab);
        this._show(tab);
    }

    _show(tab) {
        this.tabTargets.forEach(t => t.classList.toggle('active', t.dataset.tab === tab));
        this.panelTargets.forEach(p => { p.hidden = p.dataset.tab !== tab; });
    }
}
