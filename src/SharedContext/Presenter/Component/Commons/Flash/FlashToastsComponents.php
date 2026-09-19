<?php

declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Component\Commons\Flash;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\SharedContext\Domain\Enum\FlashEnum;

#[AsTwigComponent(
    name: 'FlashToast',
    template: 'shared/components/flash/flash_toasts.html.twig'
)]
final class FlashToastsComponents
{
    public function getClasses(string $type): string
    {
        return match ($this->normalize($type)) {
            FlashEnum::SUCCESS => 'border-emerald-200/80 bg-emerald-50 text-emerald-950 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-50',
            FlashEnum::WARNING => 'border-amber-200/80 bg-amber-50 text-amber-950 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-50',
            FlashEnum::DANGER => 'border-red-200/80 bg-red-50 text-red-950 dark:border-red-900 dark:bg-red-950 dark:text-red-50',
            FlashEnum::INFO => 'border-sky-200/80 bg-sky-50 text-sky-950 dark:border-sky-900 dark:bg-sky-950 dark:text-sky-50',
        };
    }

    public function getIconClasses(string $type): string
    {
        return match ($this->normalize($type)) {
            FlashEnum::SUCCESS => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
            FlashEnum::WARNING => 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
            FlashEnum::DANGER => 'bg-red-500/10 text-red-700 dark:text-red-300',
            FlashEnum::INFO => 'bg-sky-500/10 text-sky-700 dark:text-sky-300',
        };
    }

    public function getIcon(string $type): string
    {
        return match ($this->normalize($type)) {
            FlashEnum::SUCCESS => 'lucide:circle-check',
            FlashEnum::WARNING => 'lucide:triangle-alert',
            FlashEnum::DANGER => 'lucide:circle-x',
            FlashEnum::INFO => 'lucide:info',
        };
    }

    public function getLabel(string $type): string
    {
        return match ($this->normalize($type)) {
            FlashEnum::SUCCESS => 'Succès',
            FlashEnum::INFO => 'Information',
            FlashEnum::WARNING => 'Attention',
            FlashEnum::DANGER => 'Erreur',
        };
    }

    public function getRole(string $type): string
    {
        return $this->normalize($type) === FlashEnum::DANGER ? 'alert' : 'status';
    }

    public function getAriaLive(string $type): string
    {
        return $this->normalize($type) === FlashEnum::DANGER ? 'assertive' : 'polite';
    }

    private function normalize(string $type): FlashEnum
    {
        if ($type === 'error') {
            return FlashEnum::DANGER;
        }

        return FlashEnum::tryFrom($type) ?? FlashEnum::INFO;
    }
}
