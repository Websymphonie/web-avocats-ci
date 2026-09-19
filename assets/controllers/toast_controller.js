import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        timeout: {
            type: Number,
            default: 5000,
        },
    };

    connect() {
        this.handleAnimationEnd = this.handleAnimationEnd.bind(this);
        this.remainingTime = this.timeoutValue;
        this.isClosing = false;
        this.startTimer();
    }

    disconnect() {
        this.clearDismissTimer();
        clearTimeout(this.removeTimer);
        this.element.removeEventListener('animationend', this.handleAnimationEnd);
    }

    pause() {
        if (this.isClosing || this.dismissTimer === null) {
            return;
        }

        this.remainingTime = Math.max(0, this.remainingTime - (performance.now() - this.startedAt));
        this.clearDismissTimer();
    }

    resume() {
        if (this.isClosing || this.dismissTimer !== null) {
            return;
        }

        this.startTimer();
    }

    close() {
        if (this.isClosing) {
            return;
        }

        this.isClosing = true;
        this.clearDismissTimer();
        this.element.setAttribute('aria-hidden', 'true');
        this.element.style.animationFillMode = 'forwards';
        this.element.addEventListener('animationend', this.handleAnimationEnd);
        this.element.classList.remove(
            'animate-in',
            'slide-in-from-right',
        );
        this.element.classList.add(
            'animate-out',
            'fade-out',
            'slide-out-to-right',
        );

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            this.removeElement();
            return;
        }

        this.removeTimer = setTimeout(() => this.removeElement(), 500);
    }

    startTimer() {
        if (this.remainingTime <= 0) {
            this.close();
            return;
        }

        this.startedAt = performance.now();
        this.dismissTimer = setTimeout(() => this.close(), this.remainingTime);
    }

    clearDismissTimer() {
        clearTimeout(this.dismissTimer);
        this.dismissTimer = null;
    }

    handleAnimationEnd(event) {
        if (event.target === this.element && this.isClosing) {
            this.removeElement();
        }
    }

    removeElement() {
        clearTimeout(this.removeTimer);
        this.element.removeEventListener('animationend', this.handleAnimationEnd);
        this.element.remove();
    }
}
