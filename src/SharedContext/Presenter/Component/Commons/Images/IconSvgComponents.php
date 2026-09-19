<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Component\Commons\Images;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('IconSvg', template: 'shared/components/images/icon_svg_component.html.twig')]
class IconSvgComponents
{
    public string $name;
    public ?string $className = null;
    public ?string $style = 'height: 15px;';

    public function mount(string $name): void
    {
        $this->name = $name;
    }
}