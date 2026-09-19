<?php

declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Component\Modals;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(
    name: 'DeleteFormComponent',
    template: 'shared/components/modals/delete_form_component.html.twig'
)]
final class DeleteFormComponent
{
    public string $id;

    /** Identifiant visuel optionnel lorsque la même action existe dans plusieurs vues responsives. */
    public ?string $instanceId = null;

    public string $urlPath = 'javascript:void(0)';

    public string $confirmText = 'Êtes-vous sûr de vouloir supprimer cet enregistrement ?';

    public ?string $description = 'Cette action est définitive et ne peut pas être annulée.';

    public string $buttonLabel = 'Supprimer';

    public string $cancelLabel = 'Annuler';

    public string $variant = 'destructive';

    /**
     * Permet de personnaliser le texte du bouton final
     * indépendamment du bouton qui ouvre le dialogue.
     */
    public string $confirmButtonLabel = 'Supprimer';

    /**
     * Identifiant utilisé pour générer le token CSRF.
     */
    public string $csrfTokenId = 'delete';
}
