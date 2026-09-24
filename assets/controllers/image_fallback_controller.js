import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        fallbackUrl: String,
    };

    replace(event) {
        const image = event.currentTarget;

        if (image.dataset.fallbackApplied === 'true') {
            image.hidden = true;
            return;
        }

        image.dataset.fallbackApplied = 'true';
        image.src = this.fallbackUrlValue;
    }
}
