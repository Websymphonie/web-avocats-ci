import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['trigger', 'dialog'];

    static values = {
        open: Boolean,
    };

    connect() {
        this.restoreFocusBound = this.restoreFocus.bind(this);
        this.dialogTarget.addEventListener('close', this.restoreFocusBound);

        if (this.openValue) {
            this.open();
        }
    }

    disconnect() {
        this.dialogTarget.removeEventListener('close', this.restoreFocusBound);
    }

    open(event) {
        if (event?.currentTarget instanceof HTMLElement) {
            this.activeTrigger = event.currentTarget;
        }

        this.dialogTarget.hidden = false;
        this.dialogTarget.showModal();

        if (this.hasTriggerTarget) {
            this.triggerTarget.setAttribute('aria-expanded', 'true');
        }

        window.requestAnimationFrame(() => this.focusInitialElement());
    }

    close() {
        if (this.dialogTarget.open) {
            this.dialogTarget.close();
        }

        this.dialogTarget.hidden = true;

        if (this.hasTriggerTarget) {
            this.triggerTarget.setAttribute('aria-expanded', 'false');
        }
    }

    focusInitialElement() {
        const initialFocus = this.dialogTarget.querySelector('[data-alert-dialog-initial-focus]');
        const focusable = this.dialogTarget.querySelector(
            'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
        );

        (initialFocus instanceof HTMLElement ? initialFocus : focusable)?.focus({preventScroll: true});
    }

    restoreFocus() {
        this.dialogTarget.hidden = true;

        if (this.hasTriggerTarget) {
            this.triggerTarget.setAttribute('aria-expanded', 'false');
        }

        const trigger = this.activeTrigger ?? (this.hasTriggerTarget ? this.triggerTarget : null);

        if (trigger instanceof HTMLElement && trigger.isConnected) {
            trigger.focus({preventScroll: true});
        }

        this.activeTrigger = null;
    }
}
