<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Component\Commons;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('NumberFormat', template: 'shared/components/number_format_component.html.twig')]
class NumberFormatComponents
{
    public float|int $number = 0;
}