import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['slide', 'current', 'total'];

    static values = {
        interval: {type: Number, default: 6000},
    };

    connect() {
        this.index = 0;
        this.timer = null;
        this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        this.show(this.index);

        this.pauseHandler = this.pause.bind(this);
        this.resumeHandler = this.resume.bind(this);
        this.element.addEventListener('mouseenter', this.pauseHandler);
        this.element.addEventListener('focusin', this.pauseHandler);
        this.element.addEventListener('mouseleave', this.resumeHandler);
        this.element.addEventListener('focusout', this.resumeHandler);

        if (!this.reducedMotion && this.slideTargets.length > 1) {
            this.start();
        }
    }

    disconnect() {
        this.stop();
        this.element.removeEventListener('mouseenter', this.pauseHandler);
        this.element.removeEventListener('focusin', this.pauseHandler);
        this.element.removeEventListener('mouseleave', this.resumeHandler);
        this.element.removeEventListener('focusout', this.resumeHandler);
    }

    next() {
        this.show(this.index + 1);
        this.restart();
    }

    previous() {
        this.show(this.index - 1);
        this.restart();
    }

    followLink(event) {
        event.preventDefault();
        this.show(Number(event.currentTarget.dataset.index));
        this.restart();
    }

    show(index) {
        const total = this.slideTargets.length;

        if (total === 0) {
            return;
        }

        this.index = (index + total) % total;

        const activeSlide = this.slideTargets[this.index];
        if (activeSlide?.dataset.image) {
            this.element.style.setProperty('--homepage-carousel-image', `url("${activeSlide.dataset.image}")`);
        }

        this.slideTargets.forEach((slide, slideIndex) => {
            const active = slideIndex === this.index;
            slide.hidden = !active;
            slide.setAttribute('aria-hidden', String(!active));
        });

        if (this.hasCurrentTarget) {
            this.currentTarget.textContent = String(this.index + 1).padStart(2, '0');
        }

        if (this.hasTotalTarget) {
            this.totalTarget.textContent = String(total).padStart(2, '0');
        }
    }

    start() {
        this.stop();
        this.timer = window.setInterval(() => this.show(this.index + 1), this.intervalValue);
    }

    stop() {
        if (this.timer !== null) {
            window.clearInterval(this.timer);
            this.timer = null;
        }
    }

    pause() {
        this.stop();
    }

    resume() {
        if (!this.reducedMotion && this.slideTargets.length > 1) {
            this.start();
        }
    }

    restart() {
        if (!this.reducedMotion && this.slideTargets.length > 1) {
            this.start();
        }
    }
}
