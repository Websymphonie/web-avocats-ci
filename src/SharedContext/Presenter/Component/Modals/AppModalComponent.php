<?php

declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Component\Modals;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(
    name: 'AppModal',
    template: 'shared/components/modals/app_modal_component.html.twig',
)]
final class AppModalComponent
{
    public string $id;

    public string $title;

    public ?string $description = null;

    /**
     * Valeurs possibles :
     * sm, md, lg, xl, 2xl, full
     */
    public string $size = 'md';

    public bool $showCloseButton = true;

    public bool $closeOnBackdrop = true;
}