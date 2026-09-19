<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Component\Layouts;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('Header', template: 'layouts/components/header_component.html.twig')]
class HeaderComponents
{
    public string $title = 'Tableau de bord';
}
