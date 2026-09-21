import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['toggle', 'menu', 'menuIcon', 'closeIcon', 'firstLink'];

    connect() {
        this.handleKeydown = this.handleKeydown.bind(this);
        window.addEventListener('keydown', this.handleKeydown);
        this.setOpen(false, false);
    }

    disconnect() {
        window.removeEventListener('keydown', this.handleKeydown);
    }

    toggle(event) {
        event.preventDefault();
        this.setOpen(this.menuTarget.hidden);
    }

    close() {
        this.setOpen(false);
    }

    handleKeydown(event) {
        if (event.key === 'Escape' && !this.menuTarget.hidden) {
            event.preventDefault();
            this.setOpen(false);
        }
    }

    setOpen(open, restoreFocus = true) {
        this.menuTarget.hidden = !open;
        this.toggleTarget.setAttribute('aria-expanded', String(open));
        this.toggleTarget.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
        this.menuIconTarget.classList.toggle('hidden', open);
        this.closeIconTarget.classList.toggle('hidden', !open);

        if (open) {
            this.firstLinkTarget?.focus();
        } else if (restoreFocus) {
            this.toggleTarget.focus();
        }
    }
}
