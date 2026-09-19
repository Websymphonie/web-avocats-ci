import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['overlay', 'panel', 'compactToggle', 'flyout', 'flyoutTrigger'];

    connect() {
        this.handleOutsideClick = this.handleOutsideClick.bind(this);
        document.addEventListener('click', this.handleOutsideClick);
        this.updateCompactToggle();
    }

    disconnect() {
        document.removeEventListener('click', this.handleOutsideClick);
    }

    open() {
        this.panelTarget.classList.remove('-translate-x-full');
        this.overlayTarget.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    close(event) {
        if (this.hasOpenFlyout()) {
            const trigger = this.activeFlyoutTrigger;
            this.closeFlyouts();

            if (event?.type === 'keydown') {
                event.stopPropagation();
                trigger?.focus();
                return;
            }
        }

        this.panelTarget.classList.add('-translate-x-full');
        this.overlayTarget.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    toggleCompact() {
        if (window.innerWidth < 1024) {
            return;
        }

        const root = document.documentElement;
        const isCollapsed = root.dataset.sidebarCollapsed !== 'true';
        this.closeFlyouts();
        root.dataset.sidebarCollapsed = String(isCollapsed);

        try {
            localStorage.setItem('sidebar-collapsed', String(isCollapsed));
        } catch (error) {
            // Le mode compact reste fonctionnel même si le stockage est indisponible.
        }

        this.updateCompactToggle();
    }

    handleResize() {
        this.closeFlyouts();

        if (window.innerWidth >= 1024) {
            this.overlayTarget.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }

    toggleFlyout(event) {
        if (window.innerWidth < 1024 || document.documentElement.dataset.sidebarCollapsed !== 'true') {
            return;
        }

        event.stopPropagation();
        const trigger = event.currentTarget;
        const flyout = this.flyoutTargets.find((item) => item.dataset.sidebarFlyout === event.params.menu);

        if (!flyout) {
            return;
        }

        const wasOpen = !flyout.classList.contains('hidden');
        this.closeFlyouts();

        if (wasOpen) {
            return;
        }

        this.positionFlyout(flyout, trigger);
        flyout.classList.remove('hidden', 'invisible');
        flyout.setAttribute('aria-hidden', 'false');
        trigger.setAttribute('aria-expanded', 'true');
        this.activeFlyoutTrigger = trigger;
    }

    positionFlyout(flyout, trigger) {
        const triggerRect = trigger.getBoundingClientRect();
        flyout.classList.remove('hidden');
        flyout.classList.add('invisible');

        const margin = 12;
        const top = Math.min(
            Math.max(margin, triggerRect.top),
            Math.max(margin, window.innerHeight - flyout.offsetHeight - margin),
        );

        const sidebarRight = this.panelTarget.getBoundingClientRect().right;
        flyout.style.left = `${Math.max(triggerRect.right, sidebarRight) + margin}px`;
        flyout.style.top = `${top}px`;
    }

    closeFlyouts() {
        this.flyoutTargets.forEach((flyout) => {
            flyout.classList.add('hidden');
            flyout.classList.remove('invisible');
            flyout.setAttribute('aria-hidden', 'true');
        });

        this.flyoutTriggerTargets.forEach((trigger) => trigger.setAttribute('aria-expanded', 'false'));
        this.activeFlyoutTrigger = null;
    }

    handleOutsideClick(event) {
        if (!this.hasOpenFlyout()) {
            return;
        }

        const clickedInsideFlyout = this.flyoutTargets.some((flyout) => flyout.contains(event.target));
        const clickedTrigger = this.flyoutTriggerTargets.some((trigger) => trigger.contains(event.target));

        if (!clickedInsideFlyout && !clickedTrigger) {
            this.closeFlyouts();
        }
    }

    hasOpenFlyout() {
        return this.flyoutTargets.some((flyout) => !flyout.classList.contains('hidden'));
    }

    updateCompactToggle() {
        if (!this.hasCompactToggleTarget) {
            return;
        }

        const isCollapsed = document.documentElement.dataset.sidebarCollapsed === 'true';
        this.compactToggleTarget.setAttribute('aria-expanded', String(!isCollapsed));
        this.compactToggleTarget.setAttribute('aria-label', isCollapsed ? 'Déployer la navigation' : 'Réduire la navigation');
        this.compactToggleTarget.setAttribute('title', isCollapsed ? 'Déployer la navigation' : 'Réduire la navigation');
    }
}
