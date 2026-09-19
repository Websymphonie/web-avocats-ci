import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.clickOutsideBound = this.clickOutside.bind(this);
        this.keydownBound = this.keydown.bind(this);
        this.viewportChangeBound = this.viewportChange.bind(this);
        document.addEventListener('click', this.clickOutsideBound);
        document.addEventListener('keydown', this.keydownBound);
        window.addEventListener('resize', this.viewportChangeBound);
        window.addEventListener('scroll', this.viewportChangeBound, true);
        this.syncExpandedState();
    }

    disconnect() {
        document.removeEventListener('click', this.clickOutsideBound);
        document.removeEventListener('keydown', this.keydownBound);
        window.removeEventListener('resize', this.viewportChangeBound);
        window.removeEventListener('scroll', this.viewportChangeBound, true);
    }

    toggle() {
        this.syncExpandedState();
        const menu = this.element.querySelector('[data-dropdown-menu]');

        if (this.element.open) {
            if (menu) {
                menu.hidden = false;
            }
            window.requestAnimationFrame(() => this.positionMenu());
        } else if (menu) {
            menu.hidden = true;
        }
    }

    select(event) {
        if (!this.element.open) {
            return;
        }

        const action = event.target.closest('[role="menu"] a, [role="menu"] button');
        if (!action || action.dataset.action?.includes('alert-dialog#open')) {
            return;
        }

        window.setTimeout(() => this.close(false));
    }

    viewportChange() {
        if (this.element.open && this.element.querySelector('[data-dropdown-menu]')) {
            this.positionMenu();
        }
    }

    positionMenu() {
        const menu = this.element.querySelector('[data-dropdown-menu]');
        const trigger = this.element.querySelector('summary');

        if (!menu || !trigger) {
            return;
        }

        const triggerRect = trigger.getBoundingClientRect();
        const menuRect = menu.getBoundingClientRect();
        const margin = 8;
        const left = Math.max(margin, Math.min(triggerRect.right - menuRect.width, window.innerWidth - menuRect.width - margin));
        let top = triggerRect.bottom + margin;

        if (top + menuRect.height > window.innerHeight - margin && triggerRect.top > menuRect.height + margin) {
            top = triggerRect.top - menuRect.height - margin;
        }

        menu.style.left = `${left}px`;
        menu.style.top = `${Math.max(margin, top)}px`;
    }

    clickOutside(event) {
        if (this.element.open && !this.element.contains(event.target)) {
            this.close(false);
        }
    }

    keydown(event) {
        if (event.key !== 'Escape' || !this.element.open) {
            return;
        }

        event.preventDefault();
        this.close(true);
    }

    close(restoreFocus) {
        this.element.open = false;
        const menu = this.element.querySelector('[data-dropdown-menu]');
        if (menu) {
            menu.hidden = true;
        }
        this.syncExpandedState();

        if (restoreFocus) {
            this.element.querySelector('summary')?.focus();
        }
    }

    syncExpandedState() {
        this.element.querySelector('summary')?.setAttribute('aria-expanded', this.element.open ? 'true' : 'false');
    }
}
