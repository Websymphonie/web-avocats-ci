import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['wave'];

    connect() {
        this.pointerQuery = window.matchMedia('(hover: hover) and (pointer: fine)');
        this.motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
        this.navigationHandler = this.handleNavigation.bind(this);
        document.addEventListener('click', this.navigationHandler, true);

        if (!this.pointerQuery.matches || this.motionQuery.matches) {
            return;
        }

        this.moveHandler = this.move.bind(this);
        this.clickHandler = this.click.bind(this);
        this.leaveHandler = this.leave.bind(this);

        document.addEventListener('pointermove', this.moveHandler, {passive: true});
        document.addEventListener('pointerdown', this.clickHandler);
        this.element.addEventListener('pointerleave', this.leaveHandler);
    }

    disconnect() {
        document.removeEventListener('pointermove', this.moveHandler);
        document.removeEventListener('pointerdown', this.clickHandler);
        document.removeEventListener('click', this.navigationHandler, true);
        this.element.removeEventListener('pointerleave', this.leaveHandler);
    }

    move(event) {
        if (event.pointerType !== 'mouse') {
            return;
        }

        if (!this.element.contains(event.target)) {
            this.leave();
            return;
        }

        this.waveTarget.style.setProperty('--wave-x', `${event.clientX}px`);
        this.waveTarget.style.setProperty('--wave-y', `${event.clientY}px`);
        this.waveTarget.classList.add('is-active');
    }

    click(event) {
        if (event.pointerType !== 'mouse' || event.button !== 0 || !this.element.contains(event.target)) {
            return;
        }

        this.waveTarget.style.setProperty('--wave-x', `${event.clientX}px`);
        this.waveTarget.style.setProperty('--wave-y', `${event.clientY}px`);
        this.restartClickAnimation();
    }

    handleNavigation(event) {
        const link = event.target instanceof Element ? event.target.closest('a[href]') : null;

        if (!link || !this.element.contains(link)) {
            return;
        }

        const destination = new URL(link.href, window.location.href);
        const isInternalAnchor = destination.origin === window.location.origin
            && destination.pathname === window.location.pathname
            && destination.search === window.location.search
            && destination.hash !== '';

        if (isInternalAnchor) {
            event.preventDefault();
        }
    }

    leave() {
        this.waveTarget.classList.remove('is-active');
    }

    restartClickAnimation() {
        this.waveTarget.classList.remove('is-clicking');
        void this.waveTarget.offsetWidth;
        this.waveTarget.classList.add('is-clicking');
    }
}
