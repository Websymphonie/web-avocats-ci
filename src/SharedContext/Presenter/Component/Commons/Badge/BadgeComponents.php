<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Component\Commons\Badge;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\SharedContext\Domain\Enum\ColorEnum;

#[AsTwigComponent('BadgeComponent', template: 'shared/components/badges/badge_component.html.twig')]
class BadgeComponents
{
    public string $value;
    public string $type;

    public function mount(string $value): void
    {
        $this->value = $value;
    }

    public function variant(): string
    {
        return ColorEnum::tryFrom($this->type)->badgeVariant();
    }
}