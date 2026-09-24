import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['items', 'count'];
    static values = { prototype: String, nextIndex: Number, placeholder: String };

    connect() {
        this.updateCount();
    }

    add(event) {
        event.preventDefault();
        const index = this.nextIndexValue;
        this.nextIndexValue += 1;
        const html = this.prototypeValue.replaceAll(this.placeholderValue, String(index));
        this.itemsTarget.insertAdjacentHTML('beforeend', html);
        this.updateCount();

        const newItem = this.itemsTarget.lastElementChild;
        const firstField = newItem?.querySelector('input:not([type="hidden"]), select, textarea');

        requestAnimationFrame(() => {
            newItem?.scrollIntoView({
                behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
                block: 'center',
            });
            firstField?.focus({ preventScroll: true });
        });
    }

    remove(event) {
        event.preventDefault();
        event.stopPropagation();
        event.currentTarget.closest('[data-collection-item]')?.remove();
        this.updateCount();
    }

    updateCount() {
        if (!this.hasCountTarget) {
            return;
        }

        const count = this.itemsTarget.querySelectorAll(':scope > [data-collection-item]').length;
        this.countTarget.textContent = `${count} personne${count === 1 ? '' : 's'}`;
    }
}
