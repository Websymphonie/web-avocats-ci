import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'preview', 'remove'];

    choose() {
        this.inputTarget.click();
    }

    changed() {
        if (this.hasRemoveTarget) this.removeTarget.checked = false;
        const file = this.inputTarget.files?.[0];
        if (!file) return;
        this.render(URL.createObjectURL(file), file.name);
    }

    remove() {
        this.inputTarget.value = '';
        if (this.hasRemoveTarget) this.removeTarget.checked = true;
        this.previewTarget.replaceChildren();
    }

    render(url, label) {
        this.previewTarget.replaceChildren();
        const image = document.createElement('img');
        image.src = url;
        image.alt = label || 'Aperçu de l’image de couverture';
        image.className = 'h-36 w-full rounded-lg object-cover';
        this.previewTarget.append(image);
        if (label) {
            const caption = document.createElement('p');
            caption.className = 'mt-2 truncate text-xs text-muted-foreground';
            caption.textContent = label;
            this.previewTarget.append(caption);
        }
    }
}
