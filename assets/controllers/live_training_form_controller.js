import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['mode', 'physical', 'online'];

    connect() {
        this.refresh();
    }

    modeChanged() {
        this.refresh();
    }

    refresh() {
        const mode = this.modeTarget.value;
        this.toggle(this.physicalTarget, mode === 'IN_PERSON' || mode === 'HYBRID');
        this.toggle(this.onlineTarget, mode === 'ONLINE' || mode === 'HYBRID');
    }

    toggle(container, visible) {
        container.hidden = !visible;
        container.querySelectorAll('input, textarea').forEach((field) => {
            field.disabled = !visible;
        });
    }
}
