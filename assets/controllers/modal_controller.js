import { Controller } from '@hotwired/stimulus';

/*
 * Petite modale native (<dialog>) : une ligne du tableau l'ouvre au clic,
 * et elle se ferme via le bouton "Fermer", la croix, un clic sur le fond,
 * ou la touche Échap (gérée nativement par <dialog>).
 */
export default class extends Controller {
    static targets = ['dialog'];

    open() {
        if (!this.dialogTarget.open) {
            this.dialogTarget.showModal();
        }
    }

    close() {
        this.dialogTarget.close();
    }

    // Clic sur le fond (::backdrop) : la cible du clic est alors <dialog>
    // lui-même, jamais un de ses enfants.
    closeOnBackdrop(event) {
        if (event.target === this.dialogTarget) {
            this.dialogTarget.close();
        }
    }

    // Empêche un clic dans la modale (ou sur le bouton "Supprimer" de la
    // ligne) de remonter jusqu'au conteneur de la ligne et de rouvrir/
    // re-déclencher #open.
    stop(event) {
        event.stopPropagation();
    }
}
