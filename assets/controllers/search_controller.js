import { Controller } from '@hotwired/stimulus';

/*
 * Recherche instantanee : soumet le formulaire automatiquement
 * apres 400 ms sans frappe (debounce), au lieu d'exiger le bouton.
 *
 * Usage dans le template :
 *   <form data-controller="search" data-search-delay-value="400" ...>
 *       <input data-action="input->search#queueSubmit">
 *       <select data-action="change->search#queueSubmit">...</select>
 *   </form>
 */
export default class extends Controller {
    static values = { delay: { type: Number, default: 400 } };

    queueSubmit() {
        clearTimeout(this.timeout);
        this.timeout = setTimeout(() => {
            // requestSubmit() declenche l'evenement submit (et Turbo).
            this.element.requestSubmit();
        }, this.delayValue);
    }

    disconnect() {
        clearTimeout(this.timeout);
    }
}
