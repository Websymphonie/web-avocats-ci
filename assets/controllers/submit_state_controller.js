import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.resetBound = this.reset.bind(this);
        window.addEventListener('pageshow', this.resetBound);
    }

    disconnect() {
        window.removeEventListener('pageshow', this.resetBound);
    }

    submit(event) {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || form.dataset.loading === 'false') {
            return;
        }

        this.start(form, event.submitter);
    }

    start(form, submitter) {
        if (form.dataset.submitting === 'true') {
            return;
        }

        form.dataset.submitting = 'true';
        form.setAttribute('aria-busy', 'true');

        const buttons = Array.from(form.querySelectorAll('button[type="submit"], input[type="submit"]'));
        const activeButton = submitter instanceof HTMLElement
            ? submitter
            : buttons.find((button) => !button.disabled);

        buttons.forEach((button) => {
            button.dataset.submitStateDisabled = button.disabled ? 'true' : 'false';
            button.disabled = true;
        });

        if (!(activeButton instanceof HTMLButtonElement)) {
            return;
        }

        activeButton.dataset.submitStateContent = activeButton.innerHTML;
        activeButton.dataset.submitStateMinWidth = activeButton.style.minWidth;
        activeButton.style.minWidth = `${activeButton.getBoundingClientRect().width}px`;

        const spinner = document.createElement('span');
        spinner.className = 'size-4 shrink-0 animate-spin rounded-full border-2 border-current border-r-transparent';
        spinner.setAttribute('aria-hidden', 'true');

        const label = document.createElement('span');
        label.textContent = activeButton.dataset.loadingLabel
            ?? form.dataset.loadingLabel
            ?? (form.method.toLowerCase() === 'get' ? 'Recherche…' : 'Traitement…');

        activeButton.replaceChildren(spinner, label);
        activeButton.classList.add('gap-2');
    }

    reset() {
        document.querySelectorAll('form[data-submitting="true"]').forEach((form) => {
            form.removeAttribute('data-submitting');
            form.removeAttribute('aria-busy');

            form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
                button.disabled = button.dataset.submitStateDisabled === 'true';
                delete button.dataset.submitStateDisabled;

                if (button instanceof HTMLButtonElement && button.dataset.submitStateContent !== undefined) {
                    button.innerHTML = button.dataset.submitStateContent;
                    button.style.minWidth = button.dataset.submitStateMinWidth ?? '';
                    button.classList.remove('gap-2');
                    delete button.dataset.submitStateContent;
                    delete button.dataset.submitStateMinWidth;
                }
            });
        });
    }
}
