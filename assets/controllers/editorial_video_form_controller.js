import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['provider', 'videoUrl'];

    connect() { this.providerChanged(); }

    providerChanged() {
        if (!this.hasProviderTarget || !this.hasVideoUrlTarget) return;
        this.videoUrlTarget.placeholder = this.providerTarget.value === 'EXTERNAL_URL'
            ? 'https://exemple.com/video'
            : 'https://www.youtube.com/watch?v=…';
    }
}
