<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Presenter\Twig\Log;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Websymphonie\SharedContext\Domain\Enum\ColorEnum;

class LogExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('log_level_badge', [$this, 'renderLogLevelBadge'], ['is_safe' => ['html']]),
        ];
    }

    public function renderLogLevelBadge(int|string $level, string $label): string
    {
        $level = (int)$level;

        $colorLevel = match ($level) {
            200 => 'success',
            250 => 'info',
            400 => 'danger',
            default => 'secondary',
        };

        $color = ColorEnum::tryFrom($colorLevel);

        return sprintf(
            '<span class="%s">%s</span>',
            $color->badgeVariant(),
            htmlspecialchars($label, ENT_QUOTES)
        );
    }
}