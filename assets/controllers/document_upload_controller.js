import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'preview'];

    choose() { this.inputTarget.click(); }

    changed() {
        const file = this.inputTarget.files?.[0];
        this.previewTarget.replaceChildren();
        if (!file) return;
        const item = document.createElement('div');
        item.className = 'flex items-center gap-3 rounded-lg border border-border bg-muted/20 px-3 py-3 text-left';
        item.innerHTML = `<span class="inline-flex size-9 items-center justify-center rounded-lg bg-primary/10 text-primary">▣</span><span class="min-w-0"><strong class="block truncate text-sm">${this.escape(file.name)}</strong><small class="text-xs text-muted-foreground">${this.escape(file.type || 'Type détecté par le serveur')} · ${this.formatSize(file.size)}</small></span>`;
        this.previewTarget.append(item);
    }

    escape(value) { const node = document.createElement('span'); node.textContent = value; return node.innerHTML; }
    formatSize(value) { return value < 1024 * 1024 ? `${Math.max(1, Math.round(value / 1024))} Ko` : `${(value / (1024 * 1024)).toFixed(1)} Mio`; }
}
