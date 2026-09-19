import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['master', 'row', 'toolbar', 'summary', 'status', 'action', 'inputs', 'confirmation', 'confirmationDescription'];

    static values = {
        selectionLabelSingular: { type: String, default: 'élément' },
        selectionLabelPlural: { type: String, default: 'éléments' },
        inputName: { type: String, default: '' },
        inputForm: { type: String, default: '' },
        confirmationTemplate: { type: String, default: '' },
        confirmationDescriptionTemplate: { type: String, default: '' },
    };

    connect() {
        this.changeBound = this.handleChange.bind(this);
        this.element.addEventListener('change', this.changeBound);
        this.update();
    }

    disconnect() {
        this.element.removeEventListener('change', this.changeBound);
    }

    handleChange(event) {
        if (this.hasMasterTarget && event.target === this.masterTarget) {
            this.toggleAll();
            return;
        }

        if (this.rowTargets.includes(event.target)) {
            this.rowTargets
                .filter((checkbox) => checkbox.value === event.target.value)
                .forEach((checkbox) => {
                    checkbox.checked = event.target.checked;
                });
            this.update();
        }
    }

    toggleAll() {
        const checked = this.masterTarget.checked;

        this.rowTargets.forEach((checkbox) => {
            checkbox.checked = checked;
        });

        this.update();
    }

    update() {
        const selectedIds = this.selectedIds();
        const totalCount = new Set(this.rowTargets.map((checkbox) => checkbox.value)).size;
        const selectedCount = selectedIds.length;
        const hasSelection = selectedCount > 0;

        if (this.hasMasterTarget) {
            this.masterTarget.checked = totalCount > 0 && selectedCount === totalCount;
            this.masterTarget.indeterminate = hasSelection && selectedCount < totalCount;
            this.masterTarget.disabled = totalCount === 0;
            this.masterTarget.setAttribute('aria-checked', this.masterTarget.indeterminate ? 'mixed' : String(this.masterTarget.checked));
        }

        this.toolbarTargets.forEach((toolbar) => {
            toolbar.hidden = !hasSelection;
            toolbar.setAttribute('aria-hidden', String(!hasSelection));
        });

        this.summaryTargets.forEach((summary) => {
            summary.textContent = this.selectionSummary(selectedCount);
        });

        this.actionTargets.forEach((action) => {
            if (action instanceof HTMLButtonElement || action instanceof HTMLInputElement) {
                action.disabled = !hasSelection;
            } else {
                action.setAttribute('aria-disabled', String(!hasSelection));
                action.tabIndex = hasSelection ? 0 : -1;
            }
        });

        if (this.hasStatusTarget) {
            this.statusTarget.textContent = this.selectionSummary(selectedCount);
        }

        this.confirmationTargets.forEach((confirmation) => {
            confirmation.textContent = this.confirmationTemplateValue.replace('{count}', String(selectedCount));
        });

        this.confirmationDescriptionTargets.forEach((description) => {
            description.textContent = this.confirmationDescriptionTemplateValue.replace('{count}', String(selectedCount));
        });

        this.syncInputs(selectedIds);

        this.dispatch('changed', {
            detail: {
                selectedCount,
                selectedIds,
            },
        });
    }

    selectedIds() {
        return [...new Set(
            this.rowTargets
                .filter((checkbox) => checkbox.checked)
                .map((checkbox) => checkbox.value)
        )];
    }

    syncInputs(selectedIds) {
        if (!this.hasInputsTarget || !this.hasInputNameValue || this.inputNameValue === '') {
            return;
        }

        this.inputsTarget.replaceChildren(...selectedIds.map((id) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = this.inputNameValue;
            input.value = id;
            if (this.hasInputFormValue && this.inputFormValue !== '') {
                input.setAttribute('form', this.inputFormValue);
            }
            return input;
        }));
    }

    selectionSummary(selectedCount) {
        if (selectedCount === 0) {
            return 'Aucun élément sélectionné.';
        }

        const label = selectedCount === 1 ? this.selectionLabelSingularValue : this.selectionLabelPluralValue;

        return `${selectedCount} ${label} sélectionné${selectedCount === 1 ? '' : 's'}.`;
    }
}
