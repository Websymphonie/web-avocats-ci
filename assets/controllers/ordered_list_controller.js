import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["item"];

    start(event) {
        this.dragged = event.currentTarget;
        event.dataTransfer.effectAllowed = "move";
    }

    over(event) { event.preventDefault(); }

    drop(event) {
        event.preventDefault();
        const target = event.currentTarget;
        if (this.dragged && target !== this.dragged) target.before(this.dragged);
        this.dragged = null;
    }

    moveUp(event) { this.move(event.currentTarget.closest("[data-ordered-list-target='item']"), -1); }
    moveDown(event) { this.move(event.currentTarget.closest("[data-ordered-list-target='item']"), 1); }

    move(item, direction) {
        if (!item) return;
        const sibling = direction < 0 ? item.previousElementSibling : item.nextElementSibling;
        if (sibling) direction < 0 ? sibling.before(item) : sibling.after(item);
    }
}
