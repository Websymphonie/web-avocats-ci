<?php
declare(strict_types=1);

namespace Websymphonie\WebContext\Presenter\Component\Layout;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('WebHeader', template: 'web/components/layout/web_header_component.html.twig')]
class WebHeaderComponents
{
    public string $title = 'Bienvenue';
}