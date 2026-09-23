import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        if (this.element.dataset.selectComboboxReady === 'true') return;

        this.select = this.element;
        this.multiple = this.select.multiple;
        this.options = Array.from(this.select.options).map((option, index) => ({
            value: option.value,
            label: option.textContent.trim(),
            disabled: option.disabled,
            placeholder: index === 0 && option.value === '',
        }));
        this.placeholder = this.select.dataset.placeholder || this.options.find((option) => option.placeholder)?.label || 'Sélectionner…';
        this.listId = `${this.select.id || this.select.name || 'select'}-combobox-list`;
        this.opened = false;
        this.select.dataset.selectComboboxReady = 'true';
        this.select.parentElement?.querySelectorAll(':scope > .select-combobox-shell').forEach((shell) => shell.remove());
        this.build();
        this.sync();
        this.select.classList.add('select-combobox-native');
        this.select.setAttribute('aria-hidden', 'true');
        this.select.tabIndex = -1;
        this.outsideClick = (event) => {
            if (this.opened && !this.shell.contains(event.target)) this.close();
        };
        document.addEventListener('pointerdown', this.outsideClick);
    }

    disconnect() {
        document.removeEventListener('pointerdown', this.outsideClick);
        this.shell?.remove();
    }

    build() {
        this.shell = document.createElement('div');
        this.shell.className = 'select-combobox-shell';
        this.shell.dataset.disabled = this.select.disabled ? 'true' : 'false';

        this.trigger = document.createElement('button');
        this.trigger.type = 'button';
        this.trigger.className = 'select-combobox-trigger';
        this.trigger.disabled = this.select.disabled;
        this.trigger.setAttribute('role', 'combobox');
        this.trigger.setAttribute('aria-haspopup', 'listbox');
        this.trigger.setAttribute('aria-expanded', 'false');
        this.trigger.setAttribute('aria-controls', this.listId);
        this.trigger.addEventListener('click', () => this.toggle());
        this.trigger.addEventListener('keydown', (event) => this.triggerKeydown(event));

        this.label = document.createElement('span');
        this.label.className = 'select-combobox-label';
        this.trigger.append(this.label, this.makeIcon('chevron'));

        this.popover = document.createElement('div');
        this.popover.className = 'select-combobox-popover';
        this.popover.hidden = true;

        this.search = document.createElement('input');
        this.search.type = 'search';
        this.search.className = 'select-combobox-search';
        this.search.placeholder = 'Rechercher…';
        this.search.setAttribute('aria-label', `Rechercher dans ${this.select.name || 'les options'}`);
        this.search.addEventListener('input', () => this.render());
        this.search.addEventListener('keydown', (event) => this.searchKeydown(event));

        this.list = document.createElement('div');
        this.list.className = 'select-combobox-list';
        this.list.id = this.listId;
        this.list.setAttribute('role', 'listbox');
        this.list.setAttribute('aria-multiselectable', String(this.multiple));

        this.popover.append(this.search, this.list);
        this.shell.append(this.trigger, this.popover);
        this.select.before(this.shell);
    }

    render() {
        const query = this.search.value.trim().toLocaleLowerCase();
        const visible = this.options.filter((option) => !option.placeholder && (query === '' || option.label.toLocaleLowerCase().includes(query)));
        this.list.replaceChildren();
        if (visible.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'select-combobox-empty';
            empty.textContent = 'Aucun résultat.';
            this.list.append(empty);
            return;
        }
        visible.forEach((option) => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'select-combobox-option';
            item.disabled = option.disabled;
            item.dataset.value = option.value;
            item.setAttribute('role', 'option');
            item.setAttribute('aria-selected', String(this.isSelected(option.value)));
            item.append(this.makeIcon(this.isSelected(option.value) ? 'check' : 'blank'));
            const text = document.createElement('span');
            text.textContent = option.label;
            item.append(text);
            item.addEventListener('click', () => this.choose(option.value));
            this.list.append(item);
        });
    }

    choose(value) {
        const option = Array.from(this.select.options).find((candidate) => candidate.value === value);
        if (!option || option.disabled) return;
        if (this.multiple) option.selected = !option.selected;
        else this.select.value = value;
        this.select.dispatchEvent(new Event('change', {bubbles: true}));
        this.sync();
        this.render();
        if (!this.multiple) this.close();
    }

    sync() {
        const selected = Array.from(this.select.selectedOptions).filter((option) => option.value !== '');
        this.label.replaceChildren();
        if (selected.length === 0) {
            this.label.textContent = this.placeholder;
            this.label.classList.add('is-placeholder');
            return;
        }
        this.label.classList.remove('is-placeholder');
        if (this.multiple) {
            selected.slice(0, 3).forEach((option) => {
                const badge = document.createElement('span');
                badge.className = 'select-combobox-value';
                badge.textContent = option.textContent.trim();
                this.label.append(badge);
            });
            if (selected.length > 3) {
                const more = document.createElement('span');
                more.className = 'select-combobox-more';
                more.textContent = `+${selected.length - 3}`;
                this.label.append(more);
            }
        } else this.label.textContent = selected[0].textContent.trim();
    }

    isSelected(value) {
        return Array.from(this.select.selectedOptions).some((option) => option.value === value);
    }

    toggle() { this.opened ? this.close() : this.open(); }

    open() {
        if (this.trigger.disabled) return;
        this.opened = true;
        this.popover.hidden = false;
        this.trigger.setAttribute('aria-expanded', 'true');
        this.search.value = '';
        this.render();
        requestAnimationFrame(() => this.search.focus());
    }

    close() {
        this.opened = false;
        this.popover.hidden = true;
        this.trigger.setAttribute('aria-expanded', 'false');
    }

    triggerKeydown(event) {
        if (['Enter', ' ', 'ArrowDown'].includes(event.key)) {
            event.preventDefault();
            this.open();
        }
    }

    searchKeydown(event) {
        if (event.key === 'Escape') {
            event.preventDefault();
            this.close();
            this.trigger.focus();
        }
        if (event.key === 'Enter') {
            const first = this.list.querySelector('.select-combobox-option:not(:disabled)');
            if (first) {
                event.preventDefault();
                first.click();
            }
        }
    }

    makeIcon(kind) {
        const icon = document.createElement('span');
        icon.className = `select-combobox-icon select-combobox-icon-${kind}`;
        icon.setAttribute('aria-hidden', 'true');
        icon.textContent = kind === 'check' ? '✓' : kind === 'chevron' ? '⌄' : '';
        return icon;
    }
}
