import { Controller } from '@hotwired/stimulus';


export default class extends Controller {
    static targets = ['message'];

    connect() {
        if (this.hasMessageTarget) {
            setTimeout(() => {
                this.messageTarget.remove();
            }, 5000); // Disparate after 5 secondes
        }
    }
}
