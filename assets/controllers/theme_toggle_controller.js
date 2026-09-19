import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.systemPreference = window.matchMedia('(prefers-color-scheme: dark)');
        this.handleSystemPreference = this.handleSystemPreference.bind(this);
        this.handleStorage = this.handleStorage.bind(this);
        this.systemPreference.addEventListener('change', this.handleSystemPreference);
        window.addEventListener('storage', this.handleStorage);
        this.updateButton();
    }

    disconnect() {
        this.systemPreference?.removeEventListener('change', this.handleSystemPreference);
        window.removeEventListener('storage', this.handleStorage);
    }

    toggle() {
        const isDark = !document.documentElement.classList.contains('dark');

        try {
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        } catch (error) {
            // Le thème reste applicable pendant la session courante.
        }

        this.applyTheme(isDark);
    }

    handleSystemPreference(event) {
        if (this.storedTheme() === null) {
            this.applyTheme(event.matches);
        }
    }

    handleStorage(event) {
        if (event.key !== 'theme') {
            return;
        }

        const isDark = event.newValue === 'dark' || (event.newValue === null && this.systemPreference.matches);
        this.applyTheme(isDark);
    }

    storedTheme() {
        try {
            return localStorage.getItem('theme');
        } catch (error) {
            return null;
        }
    }

    applyTheme(isDark) {
        const root = document.documentElement;
        root.classList.toggle('dark', isDark);
        root.dataset.theme = isDark ? 'dark' : 'light';
        root.style.colorScheme = isDark ? 'dark' : 'light';
        this.updateButton();
    }

    updateButton() {
        const isDark = document.documentElement.classList.contains('dark');

        this.element.setAttribute('aria-label', isDark ? 'Activer le mode clair' : 'Activer le mode sombre');
        this.element.setAttribute('aria-pressed', String(isDark));
        this.element.setAttribute('title', isDark ? 'Activer le mode clair' : 'Activer le mode sombre');
    }
}
