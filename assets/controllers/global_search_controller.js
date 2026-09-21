import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'dialog',
        'input',
        'clear',
        'directAccess',
        'recent',
        'recentList',
        'resultsSection',
        'results',
        'resultCount',
        'loading',
        'empty',
        'error',
    ];

    static values = {
        endpoint: String,
        minLength: { type: Number, default: 2 },
    };

    connect() {
        this.endpointValue = this.endpointValue || '/recherche/autocomplete';
        this.activeTrigger = null;
        this.debounceTimer = null;
        this.abortController = null;
        this.resultsData = [];
        this.selectedIndex = -1;
        this.storageKey = 'avocat-ci-public-searches';
        this.onWindowKeydown = this.onWindowKeydown.bind(this);
        window.addEventListener('keydown', this.onWindowKeydown);
        this.renderRecent();
    }

    disconnect() {
        window.removeEventListener('keydown', this.onWindowKeydown);
        this.abortController?.abort();
        window.clearTimeout(this.debounceTimer);
    }

    open(event) {
        event?.preventDefault();
        this.activeTrigger = event?.currentTarget || document.activeElement;
        if (this.dialogTarget.open) {
            this.inputTarget.focus();
            return;
        }

        this.resetView();
        this.dialogTarget.showModal();
        this.activeTrigger?.setAttribute('aria-expanded', 'true');
        window.requestAnimationFrame(() => this.inputTarget.focus());
    }

    close() {
        this.abortController?.abort();
        window.clearTimeout(this.debounceTimer);
        if (this.dialogTarget.open) {
            this.dialogTarget.close();
        }
        this.restoreFocus();
    }

    handleCancel(event) {
        event.preventDefault();
        this.close();
    }

    closeOnBackdrop(event) {
        if (event.target === this.dialogTarget) {
            this.close();
        }
    }

    onWindowKeydown(event) {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            this.open({ preventDefault() {}, currentTarget: this.findSearchTrigger() });
        }
    }

    onInput() {
        const term = this.inputTarget.value.trim();
        this.clearTarget.classList.toggle('hidden', term === '');
        this.clearTarget.classList.toggle('inline-flex', term !== '');
        this.emptyTarget.classList.add('hidden');
        this.errorTarget.classList.add('hidden');
        this.resultsSectionTarget.classList.add('hidden');
        this.resultsTarget.replaceChildren();
        this.selectedIndex = -1;

        window.clearTimeout(this.debounceTimer);
        this.abortController?.abort();
        if (term.length < this.minLengthValue) {
            this.loadingTarget.classList.add('hidden');
            this.loadingTarget.classList.remove('flex');
            this.directAccessTarget.classList.remove('hidden');
            return;
        }

        this.loadingTarget.classList.remove('hidden');
        this.loadingTarget.classList.add('flex');
        this.directAccessTarget.classList.add('hidden');
        this.debounceTimer = window.setTimeout(() => this.search(term), 250);
    }

    async search(term) {
        this.abortController = new AbortController();
        try {
            const response = await fetch(`${this.endpointValue}?q=${encodeURIComponent(term)}`, {
                headers: { Accept: 'application/json' },
                signal: this.abortController.signal,
            });
            if (!response.ok) {
                throw new Error('Search request failed');
            }
            const payload = await response.json();
            this.renderResults(term, Array.isArray(payload.results) ? payload.results : []);
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }
            this.loadingTarget.classList.add('hidden');
            this.loadingTarget.classList.remove('flex');
            this.errorTarget.textContent = 'La recherche est momentanément indisponible.';
            this.errorTarget.classList.remove('hidden');
        }
    }

    navigate(event) {
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            if (this.resultsData.length === 0) return;
            event.preventDefault();
            const direction = event.key === 'ArrowDown' ? 1 : -1;
            const next = this.selectedIndex + direction;
            this.setSelectedIndex((next + this.resultsData.length) % this.resultsData.length);
        }
        if (event.key === 'Enter' && this.selectedIndex >= 0) {
            event.preventDefault();
            this.openSelectedResult();
        }
    }

    clear() {
        this.inputTarget.value = '';
        this.inputTarget.focus();
        this.onInput();
    }

    clearRecent() {
        try {
            window.localStorage.removeItem(this.storageKey);
        } catch {
            // Local storage can be unavailable in private browsing contexts.
        }
        this.renderRecent();
    }

    selectRecent(event) {
        this.inputTarget.value = event.currentTarget.dataset.query || '';
        this.inputTarget.focus();
        this.onInput();
    }

    renderResults(term, results) {
        this.loadingTarget.classList.add('hidden');
        this.loadingTarget.classList.remove('flex');
        this.resultsData = results;
        this.selectedIndex = -1;
        this.resultsTarget.replaceChildren();
        this.resultsSectionTarget.classList.remove('hidden');
        this.resultCountTarget.textContent = `${results.length} résultat${results.length > 1 ? 's' : ''}`;

        if (results.length === 0) {
            this.resultsSectionTarget.classList.add('hidden');
            this.emptyTarget.textContent = `Aucun résultat pour « ${term} »`;
            this.emptyTarget.classList.remove('hidden');
            return;
        }

        results.forEach((result, index) => {
            const link = document.createElement('a');
            link.href = result.url;
            link.dataset.index = String(index);
            link.className = 'flex items-center gap-3 border-b border-border px-4 py-3 last:border-b-0 transition hover:bg-muted focus-visible:bg-muted focus-visible:outline-none';
            link.setAttribute('aria-label', `${result.title}, ${result.metadata}`);
            link.addEventListener('mouseenter', () => this.setSelectedIndex(index));
            link.addEventListener('click', () => this.rememberQuery(term));

            const icon = document.createElement('span');
            icon.className = 'flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-xs font-bold text-primary';
            icon.textContent = this.typeInitial(result.type);
            icon.setAttribute('aria-hidden', 'true');

            const content = document.createElement('span');
            content.className = 'min-w-0 flex-1';
            const title = document.createElement('span');
            title.className = 'block truncate text-sm font-semibold text-foreground';
            title.textContent = result.title;
            const metadata = document.createElement('span');
            metadata.className = 'mt-0.5 block text-xs text-muted-foreground';
            metadata.textContent = result.metadata;
            content.append(title, metadata);

            const arrow = document.createElement('span');
            arrow.className = 'text-lg text-muted-foreground';
            arrow.textContent = '↗';
            arrow.setAttribute('aria-hidden', 'true');
            link.append(icon, content, arrow);
            this.resultsTarget.append(link);
        });
    }

    setSelectedIndex(index) {
        this.selectedIndex = index;
        [...this.resultsTarget.children].forEach((element, childIndex) => {
            element.classList.toggle('bg-muted', childIndex === index);
            element.setAttribute('aria-current', childIndex === index ? 'true' : 'false');
        });
        this.resultsTarget.children[index]?.scrollIntoView({ block: 'nearest' });
    }

    openSelectedResult() {
        const result = this.resultsData[this.selectedIndex];
        if (!result) return;
        this.rememberQuery(this.inputTarget.value.trim());
        window.location.href = result.url;
    }

    resetView() {
        this.inputTarget.value = '';
        this.clearTarget.classList.add('hidden');
        this.clearTarget.classList.remove('inline-flex');
        this.directAccessTarget.classList.remove('hidden');
        this.recentTarget.classList.remove('hidden');
        this.resultsSectionTarget.classList.add('hidden');
        this.loadingTarget.classList.add('hidden');
        this.loadingTarget.classList.remove('flex');
        this.emptyTarget.classList.add('hidden');
        this.errorTarget.classList.add('hidden');
        this.resultsTarget.replaceChildren();
        this.resultsData = [];
        this.selectedIndex = -1;
        this.renderRecent();
    }

    renderRecent() {
        const searches = this.readRecent();
        this.recentTarget.classList.toggle('hidden', searches.length === 0);
        this.recentListTarget.replaceChildren();
        searches.forEach((query) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.dataset.query = query;
            button.className = 'rounded-full border border-border px-3 py-1.5 text-sm text-muted-foreground transition hover:border-primary/50 hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring';
            button.textContent = query;
            button.setAttribute('aria-label', `Rechercher ${query}`);
            button.addEventListener('click', (event) => this.selectRecent(event));
            this.recentListTarget.append(button);
        });
    }

    rememberQuery(query) {
        const value = query.trim();
        if (value.length < this.minLengthValue) return;
        const searches = [value, ...this.readRecent().filter((item) => item.toLocaleLowerCase() !== value.toLocaleLowerCase())].slice(0, 5);
        try {
            window.localStorage.setItem(this.storageKey, JSON.stringify(searches));
        } catch {
            // Local storage is an optional enhancement.
        }
    }

    readRecent() {
        try {
            const parsed = JSON.parse(window.localStorage.getItem(this.storageKey) || '[]');
            return Array.isArray(parsed) ? parsed.filter((item) => typeof item === 'string').slice(0, 5) : [];
        } catch {
            return [];
        }
    }

    restoreFocus() {
        this.activeTrigger?.setAttribute('aria-expanded', 'false');
        const trigger = this.activeTrigger;
        this.activeTrigger = null;
        if (trigger && typeof trigger.focus === 'function') {
            window.requestAnimationFrame(() => trigger.focus());
        }
    }

    findSearchTrigger() {
        return document.querySelector('[aria-controls="public-search-dialog"]');
    }

    typeInitial(type) {
        return { news: 'A', event: 'E', training: 'F', information: 'I' }[type] || '•';
    }
}
