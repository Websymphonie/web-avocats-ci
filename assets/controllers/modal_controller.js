import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    /**
     * Ouvre un modal depuis son identifiant.
     *
     * Exemple :
     * data-action="click->modal#open"
     * data-modal-id-param="maintenance-modal"
     */
    open(event) {
        event.preventDefault();

        const modalId = event.params.id;

        if (!modalId) {
            console.error('Aucun identifiant de modal n’a été fourni.');
            return;
        }

        const dialog = document.getElementById(modalId);

        if (!(dialog instanceof HTMLDialogElement)) {
            console.error(`Le modal "${modalId}" est introuvable.`);
            return;
        }

        if (!dialog.open) {
            this.activeTrigger = event.currentTarget;
            dialog.showModal();
            document.documentElement.classList.add('overflow-hidden');
        }
    }

    /**
     * Ferme le modal contenant le bouton cliqué.
     */
    close(event) {
        event.preventDefault();

        const dialog = event.currentTarget.closest('dialog');

        if (dialog instanceof HTMLDialogElement) {
            dialog.close();
        }
    }

    /**
     * Ferme le modal lorsqu’on clique sur l’arrière-plan.
     */
    closeOnBackdrop(event) {
        const dialog = event.currentTarget;

        if (!(dialog instanceof HTMLDialogElement)) {
            return;
        }

        if (event.target === dialog) {
            dialog.close();
        }
    }

    restoreFocus() {
        document.documentElement.classList.remove('overflow-hidden');

        if (this.activeTrigger instanceof HTMLElement && this.activeTrigger.isConnected) {
            this.activeTrigger.focus({preventScroll: true});
        }

        this.activeTrigger = null;
    }
}
