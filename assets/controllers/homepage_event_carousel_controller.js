import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['slide', 'current', 'total'];

    static values = {
        interval: {type: Number, default: 6000},
        startDelay: {type: Number, default: 0},
    };

    connect() {
        this.index = 0;
        this.timer = null;
        this.startTimer = null;
        this.initialStartPending = true;
        this.transitionTimer = null;
        this.transitionFrame = null;
        this.transitioning = false;
        this.transitionDuration = 360;
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
        this.cancelTransition();
        this.element.removeEventListener('mouseenter', this.pauseHandler);
        this.element.removeEventListener('focusin', this.pauseHandler);
        this.element.removeEventListener('mouseleave', this.resumeHandler);
        this.element.removeEventListener('focusout', this.resumeHandler);
    }

    next() {
        if (this.navigate(this.index + 1, 'next')) {
            this.restart();
        }
    }

    previous() {
        if (this.navigate(this.index - 1, 'previous')) {
            this.restart();
        }
    }

    followLink(event) {
        event.preventDefault();
        const targetIndex = Number(event.currentTarget.dataset.index);
        const direction = targetIndex >= this.index ? 'next' : 'previous';

        if (this.navigate(targetIndex, direction)) {
            this.restart();
        }
    }

    navigate(index, direction) {
        const total = this.slideTargets.length;

        if (total <= 1 || this.transitioning) {
            return false;
        }

        const nextIndex = (index + total) % total;

        if (nextIndex === this.index) {
            return false;
        }

        if (this.reducedMotion) {
            this.show(nextIndex);

            return true;
        }

        const currentSlide = this.slideTargets[this.index];
        const nextSlide = this.slideTargets[nextIndex];

        if (!currentSlide || !nextSlide) {
            this.show(nextIndex);

            return true;
        }

        const resumeAfterTransition = this.timer !== null;

        this.stop();
        this.transitioning = true;
        this.index = nextIndex;
        this.resumeAfterTransition = resumeAfterTransition;

        this.element.classList.add('is-carousel-transitioning');
        this.updateNextBackground(nextSlide);

        currentSlide.classList.add('is-carousel-exiting', `is-carousel-direction-${direction}`);
        currentSlide.setAttribute('aria-hidden', 'true');
        nextSlide.hidden = false;
        nextSlide.setAttribute('aria-hidden', 'false');
        nextSlide.classList.add('is-carousel-entering', `is-carousel-direction-${direction}`);
        this.updateIndicators(total);

        this.transitionFrame = window.requestAnimationFrame(() => {
            currentSlide.classList.add('is-carousel-animated');
            nextSlide.classList.add('is-carousel-animated');
        });

        this.transitionTimer = window.setTimeout(() => {
            this.finishTransition(currentSlide, nextSlide, total);
        }, this.transitionDuration);

        return true;
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
            slide.classList.remove(
                'is-carousel-entering',
                'is-carousel-exiting',
                'is-carousel-animated',
                'is-carousel-direction-next',
                'is-carousel-direction-previous',
            );
            slide.hidden = !active;
            slide.setAttribute('aria-hidden', String(!active));
        });

        this.updateIndicators(total);
    }

    finishTransition(currentSlide, nextSlide, total) {
        currentSlide.hidden = true;
        currentSlide.setAttribute('aria-hidden', 'true');
        nextSlide.hidden = false;
        nextSlide.setAttribute('aria-hidden', 'false');

        this.element.style.setProperty('--homepage-carousel-image', `url("${nextSlide.dataset.image}")`);
        this.element.style.removeProperty('--homepage-carousel-next-image');
        this.element.classList.remove('is-carousel-transitioning');
        this.clearSlideTransitionClasses(currentSlide);
        this.clearSlideTransitionClasses(nextSlide);
        this.transitionTimer = null;
        this.transitionFrame = null;
        this.transitioning = false;
        this.updateIndicators(total);

        if (this.resumeAfterTransition) {
            this.start();
        }

        this.resumeAfterTransition = false;
    }

    updateIndicators(total) {
        if (this.hasCurrentTarget) {
            this.currentTarget.textContent = String(this.index + 1).padStart(2, '0');
        }

        if (this.hasTotalTarget) {
            this.totalTarget.textContent = String(total).padStart(2, '0');
        }
    }

    updateNextBackground(slide) {
        if (slide.dataset.image) {
            this.element.style.setProperty('--homepage-carousel-next-image', `url("${slide.dataset.image}")`);
        }
    }

    clearSlideTransitionClasses(slide) {
        slide.classList.remove(
            'is-carousel-entering',
            'is-carousel-exiting',
            'is-carousel-animated',
            'is-carousel-direction-next',
            'is-carousel-direction-previous',
        );
    }

    cancelTransition() {
        if (this.transitionFrame !== null) {
            window.cancelAnimationFrame(this.transitionFrame);
            this.transitionFrame = null;
        }

        if (this.transitionTimer !== null) {
            window.clearTimeout(this.transitionTimer);
            this.transitionTimer = null;
        }

        this.slideTargets.forEach((slide) => {
            this.clearSlideTransitionClasses(slide);
        });
        this.element.classList.remove('is-carousel-transitioning');
        this.element.style.removeProperty('--homepage-carousel-next-image');
        this.transitioning = false;
        this.resumeAfterTransition = false;
    }

    start() {
        this.stop();

        if (this.initialStartPending && this.startDelayValue > 0) {
            this.initialStartPending = false;
            this.startTimer = window.setTimeout(() => {
                this.startTimer = null;
                this.startInterval();
            }, this.startDelayValue);

            return;
        }

        this.startInterval();
    }

    startInterval() {
        this.timer = window.setInterval(() => this.navigate(this.index + 1, 'next'), this.intervalValue);
    }

    stop() {
        if (this.startTimer !== null) {
            window.clearTimeout(this.startTimer);
            this.startTimer = null;
        }

        if (this.timer !== null) {
            window.clearInterval(this.timer);
            this.timer = null;
        }
    }

    pause() {
        this.stop();

        if (this.transitioning) {
            this.resumeAfterTransition = false;
        }
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
