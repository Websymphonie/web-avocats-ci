import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['button', 'message'];
    static values = {
        amount: Number,
        publicKey: String,
        partnerId: String,
        sandbox: Boolean,
        statusUrl: String,
    };

    connect() {
        this.loading = false;
        this.loadSdk();
    }

    async loadSdk() {
        if (window.openKkiapayWidget) {
            return;
        }

        await new Promise((resolve, reject) => {
            const existing = document.querySelector('script[data-kkiapay-sdk]');
            if (existing) {
                existing.addEventListener('load', resolve, {once: true});
                existing.addEventListener('error', reject, {once: true});
                return;
            }

            const script = document.createElement('script');
            script.src = 'https://cdn.kkiapay.me/k.js';
            script.async = true;
            script.dataset.kkiapaySdk = 'true';
            script.addEventListener('load', resolve, {once: true});
            script.addEventListener('error', reject, {once: true});
            document.head.appendChild(script);
        });
    }

    async open() {
        if (this.loading) {
            return;
        }
        this.loading = true;
        this.buttonTarget.disabled = true;
        this.messageTarget.textContent = 'Chargement du paiement…';

        try {
            await this.loadSdk();
            if (!window.openKkiapayWidget) {
                throw new Error('KkiaPay SDK unavailable');
            }

            window.addSuccessListener(() => {
                window.location.assign(this.statusUrlValue);
            });
            window.addFailedListener(() => {
                this.messageTarget.textContent = 'Le paiement n’a pas été finalisé. Le statut serveur reste la référence.';
                this.buttonTarget.disabled = false;
                this.loading = false;
            });
            window.openKkiapayWidget({
                amount: this.amountValue,
                key: this.publicKeyValue,
                partnerId: this.partnerIdValue,
                position: 'center',
                sandbox: this.sandboxValue,
            });
        } catch (error) {
            this.messageTarget.textContent = 'Le widget de paiement est indisponible. Réessayez plus tard.';
            this.buttonTarget.disabled = false;
            this.loading = false;
        }
    }
}
