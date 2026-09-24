import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['title', 'loading', 'profile', 'error'];

    static values = {
        dialogId: String,
    };

    connect() {
        this.dialog = document.getElementById(this.dialogIdValue);
        if (!(this.dialog instanceof HTMLDialogElement)) {
            return;
        }

        this.handleDialogClose = this.handleDialogClose.bind(this);
        this.dialog.addEventListener('close', this.handleDialogClose);
    }

    disconnect() {
        this.abortController?.abort();
        this.dialog?.removeEventListener('close', this.handleDialogClose);
    }

    async open(event) {
        const link = event.currentTarget;
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || (link.target && link.target !== '_self')) {
            return;
        }

        if (!(this.dialog instanceof HTMLDialogElement) || this.dialog.open) {
            return;
        }

        event.preventDefault();
        this.trigger = link;
        this.abortController?.abort();
        this.abortController = new AbortController();
        this.titleTarget.textContent = 'Chargement du profil';
        this.loadingTarget.hidden = false;
        this.loadingTarget.classList.remove('hidden');
        this.profileTarget.hidden = true;
        this.profileTarget.classList.add('hidden');
        this.profileTarget.replaceChildren();
        this.errorTarget.hidden = true;
        this.errorTarget.classList.add('hidden');
        this.dialog.setAttribute('aria-busy', 'true');

        this.dialog.showModal();
        document.documentElement.classList.add('overflow-hidden');

        try {
            const response = await fetch(link.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
                credentials: 'same-origin',
                signal: this.abortController.signal,
            });

            if (!response.ok || !response.headers.get('content-type')?.includes('text/html')) {
                throw new Error('profile-unavailable');
            }

            const responseDocument = new DOMParser().parseFromString(await response.text(), 'text/html');
            const fragment = responseDocument.querySelector('[data-lawyer-profile-fragment]');
            if (!fragment || !fragment.dataset.lawyerName) {
                throw new Error('profile-fragment-unavailable');
            }

            this.titleTarget.textContent = fragment.dataset.lawyerName;
            this.profileTarget.replaceChildren(document.importNode(fragment, true));
            this.loadingTarget.hidden = true;
            this.loadingTarget.classList.add('hidden');
            this.profileTarget.hidden = false;
            this.profileTarget.classList.remove('hidden');
        } catch (error) {
            if (error.name === 'AbortError' || !this.dialog.open) {
                return;
            }

            this.loadingTarget.hidden = true;
            this.loadingTarget.classList.add('hidden');
            this.errorTarget.hidden = false;
            this.errorTarget.classList.remove('hidden');
        } finally {
            this.dialog.setAttribute('aria-busy', 'false');
        }
    }

    handleDialogClose() {
        this.abortController?.abort();
        this.abortController = null;
        this.dialog.removeAttribute('aria-busy');
        this.profileTarget.replaceChildren();
        this.profileTarget.hidden = true;
        this.profileTarget.classList.add('hidden');
        this.loadingTarget.hidden = false;
        this.loadingTarget.classList.remove('hidden');
        this.errorTarget.hidden = true;
        this.errorTarget.classList.add('hidden');
        this.titleTarget.textContent = 'Profil avocat';
        if (document.querySelector('dialog[open]') === null) {
            document.documentElement.classList.remove('overflow-hidden');
        }

        if (this.trigger instanceof HTMLElement && this.trigger.isConnected) {
            this.trigger.focus({ preventScroll: true });
        }

        this.trigger = null;
    }
}
