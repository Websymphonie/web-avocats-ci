import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['step', 'progress', 'progressTrack', 'progressLabel', 'completionMessage', 'reset'];

    static values = {
        storageKey: String,
    };

    connect() {
        this.completedIds = new Set(this.readCompletedIds());
        this.refresh();
    }

    toggle(event) {
        const { id } = event.params;

        if (!id) {
            return;
        }

        if (this.completedIds.has(id)) {
            this.completedIds.delete(id);
        } else {
            this.completedIds.add(id);
        }

        this.persist();
        this.refresh();
    }

    reset() {
        this.completedIds.clear();
        this.persist();
        this.refresh();
    }

    refresh() {
        const validIds = new Set(this.stepTargets.map((step) => step.dataset.onboardingId));
        this.completedIds = new Set([...this.completedIds].filter((id) => validIds.has(id)));

        const completedCount = this.stepTargets.filter((step) => this.completedIds.has(step.dataset.onboardingId)).length;
        const total = this.stepTargets.length;
        const ratio = total === 0 ? 0 : Math.round((completedCount / total) * 100);

        this.progressTarget.style.width = `${ratio}%`;
        this.progressTrackTarget.setAttribute('aria-valuenow', String(completedCount));
        this.progressLabelTargets.forEach((label) => {
            label.textContent = `${completedCount} / ${total}`;
        });
        this.completionMessageTarget.classList.toggle('hidden', total === 0 || completedCount !== total);
        this.resetTarget.classList.toggle('hidden', completedCount === 0);

        this.stepTargets.forEach((step) => {
            const completed = this.completedIds.has(step.dataset.onboardingId);
            const button = step.querySelector('[data-onboarding-target="button"]');
            const label = step.querySelector('[data-onboarding-target="buttonLabel"]');

            step.dataset.completed = String(completed);

            if (button) {
                button.dataset.completed = String(completed);
                button.setAttribute('aria-pressed', String(completed));
            }

            if (label) {
                label.textContent = completed ? 'Étape terminée' : 'Marquer comme fait';
            }
        });
    }

    readCompletedIds() {
        try {
            const value = window.localStorage.getItem(this.storageKeyValue);
            const parsed = value ? JSON.parse(value) : [];

            return Array.isArray(parsed) ? parsed.filter((id) => typeof id === 'string') : [];
        } catch (error) {
            return [];
        }
    }

    persist() {
        try {
            window.localStorage.setItem(this.storageKeyValue, JSON.stringify([...this.completedIds]));
        } catch (error) {
            // La checklist reste utilisable durant la session même si le stockage est indisponible.
        }
    }
}
