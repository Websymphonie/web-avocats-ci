<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Component\Commons;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('EmptyComponent', template: 'shared/components/empty_component.html.twig')]
class EmptyComponents
{
    public string $icon = 'tabler:folder-code';
    public string $title = 'Pas de donnée disponible';
    public string $description = 'Rien à voir pour le moment...';
    public ?string $buttonLabel = null;
    public ?string $buttonUrl = null;
    public ?string $buttonVariant = 'outline';
    public ?string $buttonAs = 'a';
}