import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['search', 'item', 'clear', 'empty'];

    filter(event) {
        const rawQuery = event.currentTarget.value.trim();
        const query = this.#normalize(rawQuery);
        let visibleCount = 0;

        for (const item of this.itemTargets) {
            const matches = this.#normalize(item.dataset.listFilterName ?? '').includes(query);
            item.hidden = !matches;
            visibleCount += matches ? 1 : 0;
        }

        for (const clearButton of this.clearTargets) {
            clearButton.hidden = event.currentTarget.value.length === 0;
            clearButton.classList.toggle('hidden', clearButton.hidden);
        }

        if (this.hasEmptyTarget) {
            this.emptyTarget.hidden = visibleCount > 0;
            this.emptyTarget.classList.toggle('hidden', this.emptyTarget.hidden);
        }
    }

    clear(event) {
        event.preventDefault();
        this.searchTarget.value = '';
        this.filter({ currentTarget: this.searchTarget });
        this.searchTarget.focus();
    }

    #normalize(value) {
        return value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('fr');
    }
}
