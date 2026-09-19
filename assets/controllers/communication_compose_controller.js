import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['template', 'subject', 'body'];
    static values = {templates: Array};

    applyTemplate() {
        const selectedId = Number(this.templateTarget.value);
        const template = this.templatesValue.find((item) => Number(item.id) === selectedId);

        if (!template) {
            return;
        }

        this.subjectTarget.value = template.subject ?? '';
        this.bodyTarget.value = template.body ?? '';
        this.subjectTarget.focus({preventScroll: true});
    }
}
