<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Component\Commons\Images;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('CircleImage', template: 'shared/components/images/circle_image_component.html.twig')]
class CircleImageComponents
{
    public string $imageLink;
    public ?string $alt;

    public function mount(string $imageLink): void
    {
        $this->imageLink = $imageLink;
    }
}