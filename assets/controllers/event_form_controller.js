import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['format', 'physical', 'online'];

    connect() {
        this.refresh();
    }

    formatChanged() {
        this.refresh();
    }

    refresh() {
        const value = this.formatTarget.value;
        const physical = value === 'IN_PERSON' || value === 'HYBRID';
        const online = value === 'ONLINE' || value === 'HYBRID';

        this.toggle(this.physicalTarget, physical);
        this.toggle(this.onlineTarget, online);
    }

    toggle(element, visible) {
        element.hidden = !visible;
        element.querySelectorAll('input, textarea').forEach((field) => {
            field.disabled = !visible;
        });
    }
}
