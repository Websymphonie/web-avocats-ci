import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["input", "preview"];

    choose() { this.inputTarget.click(); }
    dragover(event) { event.preventDefault(); this.element.classList.add("border-primary", "bg-primary/5"); }
    dragleave(event) { event.preventDefault(); this.element.classList.remove("border-primary", "bg-primary/5"); }
    drop(event) { event.preventDefault(); this.element.classList.remove("border-primary", "bg-primary/5"); this.inputTarget.files = event.dataTransfer.files; this.render(); }
    changed() { this.render(); }
    render() {
        const files = Array.from(this.inputTarget.files || []);
        this.previewTarget.replaceChildren(...files.map((file) => {
            const item = document.createElement("li"); item.className = "flex min-w-0 items-center gap-3 rounded-lg border border-border bg-muted/30 px-3 py-2 text-sm";
            const image = document.createElement("img"); image.className = "size-10 rounded object-cover"; image.alt = ""; image.src = URL.createObjectURL(file); image.onload = () => URL.revokeObjectURL(image.src);
            const label = document.createElement("span"); label.className = "min-w-0 truncate"; label.textContent = file.name;
            item.append(image, label); return item;
        }));
    }
}
