import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'trigger',
        'label',
        'popover',
        'search',
        'option',
        'empty',
        'hiddenInput',
        'group',
        'clearButton',
        'empty',
    ];

    static values = {
        value: {type: String, default: ''},
        placeholder: {type: String, default: 'Select option...'},
        selectedLabel: {type: String, default: ''},
        searchUrl: {type: String, default: ''},
        minimumCharacters: {type: Number, default: 0},
        minimumMessage: {type: String, default: 'Continue typing to search.'},
        loadingMessage: {type: String, default: 'Loading…'},
        errorMessage: {type: String, default: 'Search failed. Try again.'},
        emptyMessage: {type: String, default: 'No results found.'},
    };

    #activeIndex = -1;
    #isOpen = false;
    #outsideClickHandler = null;
    #searchTimeout = null;
    #requestController = null;
    #requestVersion = 0;
    #isComposing = false;

    connect() {
        this.#outsideClickHandler = this.#onOutsideClick.bind(this);
    }

    disconnect() {
        this.#cancelRemoteSearch();
        this.#close();
    }

    toggle() {
        if (this.#isOpen) {
            this.#close();
        } else {
            this.#open();
        }
    }

    clear(event) {
        event.stopPropagation();
        this.selectedLabelValue = '';
        this.valueValue = '';
        this.dispatch('change', {detail: {value: '', label: ''}, bubbles: true});
        this.#close();
        this.triggerTarget.focus();
    }

    onSearch(event) {
        if (this.hasSearchUrlValue && this.searchUrlValue) {
            if (this.#isComposing || event.isComposing) return;
            this.#queueRemoteSearch(event.target.value);
            return;
        }

        const query = event.target.value.toLowerCase();
        let firstVisibleIndex = -1;
        let visibleCount = 0;

        const targets = this.optionTargets;
        for (let i = 0; i < targets.length; i++) {
            const matches = targets[i].dataset.label.toLowerCase().includes(query);
            targets[i].hidden = !matches;
            if (matches) {
                if (firstVisibleIndex === -1) firstVisibleIndex = i;
                visibleCount++;
            }
        }

        for (const group of this.groupTargets) {
            group.hidden = !targets.filter((o) => group.contains(o)).some((o) => !o.hidden);
        }

        this.emptyTarget.hidden = visibleCount > 0;
        this.#setActive(firstVisibleIndex);
    }

    onCompositionStart() {
        this.#isComposing = true;
        this.#cancelRemoteSearch();
    }

    onCompositionEnd(event) {
        this.#isComposing = false;
        if (this.hasSearchUrlValue && this.searchUrlValue) {
            this.#queueRemoteSearch(event.target.value);
        }
    }

    onSelect(event) {
        this.#selectOption(event.currentTarget);
    }

    onOptionHover(event) {
        const index = this.optionTargets.indexOf(event.currentTarget);
        if (index !== -1 && !event.currentTarget.hidden) {
            this.#setActive(index);
        }
    }

    onTriggerKeydown(event) {
        switch (event.key) {
            case 'ArrowDown':
                event.preventDefault();
                this.#open();
                this.#setActive(this.#firstVisibleIndex());
                break;
            case 'ArrowUp':
                event.preventDefault();
                this.#open();
                this.#setActive(this.#lastVisibleIndex());
                break;
            case 'Enter':
            case ' ':
                event.preventDefault();
                this.#open();
                break;
        }
    }

    onSearchKeydown(event) {
        if (event.isComposing || this.#isComposing || event.keyCode === 229) return;

        switch (event.key) {
            case 'ArrowDown': {
                event.preventDefault();
                const next = this.#nextVisibleIndex(this.#activeIndex);
                if (next !== -1) this.#setActive(next);
                break;
            }
            case 'ArrowUp': {
                event.preventDefault();
                const prev = this.#prevVisibleIndex(this.#activeIndex);
                if (prev !== -1) this.#setActive(prev);
                break;
            }
            case 'Home':
                event.preventDefault();
                this.#setActive(this.#firstVisibleIndex());
                break;
            case 'End':
                event.preventDefault();
                this.#setActive(this.#lastVisibleIndex());
                break;
            case 'Enter':
                event.preventDefault();
                if (this.#activeIndex !== -1) {
                    this.#selectOption(this.optionTargets[this.#activeIndex]);
                }
                break;
            case 'Escape':
                event.preventDefault();
                this.#close();
                this.triggerTarget.focus();
                break;
            case 'Tab':
                this.#close();
                break;
        }
    }

    valueValueChanged() {
        this.#syncLabel();
        this.#syncCheckIcons();
        if (this.hasHiddenInputTarget) {
            this.hiddenInputTarget.value = this.valueValue;
        }
    }

    #open() {
        if (this.#isOpen) return;
        this.#isOpen = true;

        this.searchTarget.value = '';
        if (this.hasSearchUrlValue && this.searchUrlValue) {
            for (const option of this.optionTargets) option.hidden = true;
            for (const group of this.groupTargets) group.hidden = true;
            this.emptyTarget.textContent = this.minimumCharactersValue > 0 ? this.minimumMessageValue : this.loadingMessageValue;
            this.emptyTarget.hidden = false;
            this.#setListBusy(false);
            if (this.minimumCharactersValue === 0) this.#queueRemoteSearch('');
        } else {
            for (const option of this.optionTargets) option.hidden = false;
            for (const group of this.groupTargets) group.hidden = false;
            this.emptyTarget.hidden = true;
        }
        this.#setActive(-1);

        const popover = this.popoverTarget;
        popover.hidden = false;
        popover.dataset.state = 'open';
        this.#positionPopover();
        this.triggerTarget.setAttribute('aria-expanded', 'true');

        document.addEventListener('pointerdown', this.#outsideClickHandler);

        requestAnimationFrame(() => {
            this.searchTarget.focus();
        });
    }

    #close() {
        if (!this.#isOpen) return;
        this.#isOpen = false;
        this.#cancelRemoteSearch();

        const popover = this.popoverTarget;
        popover.hidden = true;
        popover.dataset.state = 'closed';
        popover.style.cssText = '';
        this.triggerTarget.setAttribute('aria-expanded', 'false');

        document.removeEventListener('pointerdown', this.#outsideClickHandler);
    }

    #selectOption(option) {
        const {value, label} = option.dataset;
        this.selectedLabelValue = label;
        this.valueValue = value;
        this.dispatch('change', {detail: {value, label}, bubbles: true});
        this.#close();
        this.triggerTarget.focus();
    }

    #syncLabel() {
        if (!this.hasLabelTarget) return;
        const selected = this.hasOptionTarget
            ? this.optionTargets.find((o) => o.dataset.value === this.valueValue)
            : null;
        const label = this.selectedLabelValue || (selected ? selected.dataset.label : '');
        this.labelTarget.textContent = label || this.placeholderValue;
        this.labelTarget.classList.toggle('text-muted-foreground', !label);
        if (this.hasClearButtonTarget) {
            this.clearButtonTarget.hidden = !label;
        }
    }

    #syncCheckIcons() {
        if (!this.hasOptionTarget) return;
        for (const option of this.optionTargets) {
            const selected = option.dataset.value === this.valueValue;
            option.setAttribute('aria-selected', String(selected));
            const icon = option.querySelector('[data-combobox-check]');
            if (icon) {
                icon.classList.toggle('opacity-0', !selected);
                icon.classList.toggle('opacity-100', selected);
            }
        }
    }

    #queueRemoteSearch(rawQuery) {
        this.#cancelRemoteSearch();
        const query = rawQuery.trim();
        this.#clearRemoteOptions();
        this.#setActive(-1);

        if (query.length < this.minimumCharactersValue) {
            this.emptyTarget.textContent = this.minimumMessageValue;
            this.emptyTarget.hidden = false;
            this.#setListBusy(false);
            return;
        }

        this.emptyTarget.textContent = this.loadingMessageValue;
        this.emptyTarget.hidden = false;
        this.#setListBusy(true);
        const requestVersion = this.#requestVersion;
        this.#searchTimeout = window.setTimeout(() => {
            void this.#loadRemoteResults(query, requestVersion);
        }, 300);
    }

    async #loadRemoteResults(query, requestVersion) {
        const controller = new AbortController();
        this.#requestController = controller;

        try {
            const url = new URL(this.searchUrlValue, window.location.origin);
            url.searchParams.set('query', query);
            const response = await fetch(url, {
                signal: controller.signal,
                headers: {Accept: 'application/json'},
            });
            if (!response.ok) throw new Error(`Autocomplete request failed (${response.status}).`);

            const payload = await response.json();
            if (requestVersion !== this.#requestVersion || this.searchTarget.value.trim() !== query) return;

            const results = Array.isArray(payload.results) ? payload.results : [];
            this.#clearRemoteOptions();
            results.forEach((result, index) => this.#appendRemoteOption(result, index));

            this.emptyTarget.textContent = this.emptyMessageValue;
            this.emptyTarget.hidden = results.length > 0;
            this.#setListBusy(false);
            requestAnimationFrame(() => {
                if (requestVersion === this.#requestVersion) this.#setActive(this.#firstVisibleIndex());
            });
        } catch (error) {
            if (error.name === 'AbortError' || requestVersion !== this.#requestVersion) return;
            this.emptyTarget.textContent = this.errorMessageValue;
            this.emptyTarget.hidden = false;
            this.#setListBusy(false);
        }
    }

    #appendRemoteOption(result, index) {
        if (result?.value === undefined || result?.text === undefined) return;

        const option = document.createElement('div');
        const listboxId = this.searchTarget.getAttribute('aria-controls') || 'combobox_listbox';
        option.id = `${listboxId}_remote_option_${index}`;
        option.setAttribute('role', 'option');
        option.setAttribute('aria-selected', String(String(result.value) === this.valueValue));
        option.dataset.comboboxTarget = 'option';
        option.dataset.value = String(result.value);
        option.dataset.label = String(result.text);
        option.dataset.remoteOption = 'true';
        option.dataset.action = 'click->combobox#onSelect mouseenter->combobox#onOptionHover';
        option.className = 'relative flex cursor-pointer select-none items-center gap-2 rounded-sm px-2 py-1.5 text-sm outline-none data-[active]:bg-accent data-[active]:text-accent-foreground';

        const check = document.createElement('span');
        check.dataset.comboboxCheck = '';
        check.setAttribute('aria-hidden', 'true');
        check.className = `size-4 ${String(result.value) === this.valueValue ? 'opacity-100' : 'opacity-0'}`;
        check.textContent = '✓';

        const label = document.createElement('span');
        label.textContent = String(result.text);
        option.append(check, label);
        this.popoverTarget.querySelector('[role="listbox"]').append(option);
    }

    #clearRemoteOptions() {
        this.popoverTarget.querySelectorAll('[data-remote-option="true"]').forEach((option) => option.remove());
    }

    #setListBusy(isBusy) {
        this.popoverTarget.querySelector('[role="listbox"]')?.setAttribute('aria-busy', String(isBusy));
    }

    #cancelRemoteSearch() {
        if (this.#searchTimeout !== null) {
            window.clearTimeout(this.#searchTimeout);
            this.#searchTimeout = null;
        }
        this.#requestVersion++;
        this.#requestController?.abort();
        this.#requestController = null;
    }

    #setActive(index) {
        if (this.#activeIndex !== -1 && this.optionTargets[this.#activeIndex]) {
            delete this.optionTargets[this.#activeIndex].dataset.active;
        }

        this.#activeIndex = index;

        if (index === -1) {
            if (this.hasSearchTarget) {
                this.searchTarget.removeAttribute('aria-activedescendant');
            }
            return;
        }

        const option = this.optionTargets[index];
        if (!option) return;

        option.dataset.active = '';
        this.searchTarget.setAttribute('aria-activedescendant', option.id);
        option.scrollIntoView({block: 'nearest'});
    }

    #firstVisibleIndex() {
        return this.optionTargets.findIndex((o) => !o.hidden);
    }

    #lastVisibleIndex() {
        const targets = this.optionTargets;
        for (let i = targets.length - 1; i >= 0; i--) {
            if (!targets[i].hidden) return i;
        }
        return -1;
    }

    #nextVisibleIndex(from) {
        const targets = this.optionTargets;
        for (let i = from + 1; i < targets.length; i++) {
            if (!targets[i].hidden) return i;
        }
        return from;
    }

    #prevVisibleIndex(from) {
        const targets = this.optionTargets;
        for (let i = from - 1; i >= 0; i--) {
            if (!targets[i].hidden) return i;
        }
        return from;
    }

    #positionPopover() {
        const triggerRect = this.triggerTarget.getBoundingClientRect();
        const popover = this.popoverTarget;
        const popoverHeight = popover.offsetHeight;

        popover.style.position = 'fixed';
        popover.style.width = `${triggerRect.width}px`;
        popover.style.left = `${triggerRect.left}px`;
        popover.style.zIndex = '50';

        const spaceBelow = window.innerHeight - triggerRect.bottom;
        if (spaceBelow < popoverHeight && triggerRect.top > spaceBelow) {
            popover.style.top = `${Math.max(0, triggerRect.top - popoverHeight)}px`;
        } else {
            popover.style.top = `${triggerRect.bottom}px`;
        }
    }

    #onOutsideClick(event) {
        if (!this.element.contains(event.target) && !this.popoverTarget.contains(event.target)) {
            this.#close();
        }
    }
}
