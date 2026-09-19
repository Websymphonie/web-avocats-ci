<?php

declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Component\Commons;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(
    'BooleanComponent',
    template: 'shared/components/boolean_component.html.twig'
)]
final class BooleanComponents
{
    public bool|int $action = false;

    public string $labelTrue = 'Oui';
    public string $labelFalse = 'Non';

    public string $colorTrue = 'success';
    public string $colorFalse = 'danger';

    public function getLabel(): string
    {
        return $this->action
            ? $this->labelTrue
            : $this->labelFalse;
    }

    public function getVariant(): string
    {
        return match ($this->getColor()) {
            'success' => 'bg-green-400/10 text-green-600 inset-ring-green-500/20',
            'danger' => 'bg-red-400/10 text-red-600 inset-ring-red-400/20',
            default => 'bg-gray-400/10 text-gray-600 inset-ring-gray-400/20',
        };
    }

    public function getColor(): string
    {
        return $this->action
            ? $this->colorTrue
            : $this->colorFalse;
    }
}