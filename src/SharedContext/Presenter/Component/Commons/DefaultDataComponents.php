<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Component\Commons;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('DefaultDataComponent', template: 'shared/components/default_data_component.html.twig')]
class DefaultDataComponents
{
    public ?string $label = 'Aucun(e)';
}