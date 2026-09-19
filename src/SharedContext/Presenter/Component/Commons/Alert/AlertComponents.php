<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Component\Commons\Alert;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('AlertComponent', template: 'shared/components/alert/alert_component.html.twig')]
class AlertComponents
{
    public ?string $title = null;

    public string $message;

    public string $type = 'info';

    public function getIcon(): string
    {
        return match ($this->type) {
            'success' => 'lucide:circle-check',
            'warning' => 'lucide:triangle-alert',
            'danger' => 'lucide:circle-x',
            default => 'lucide:info',
        };
    }

    public function getClasses(): string
    {
        return match ($this->type) {
            'success' => 'border-green-200 bg-green-50 text-green-900 dark:border-green-900 dark:bg-green-950 dark:text-green-50',

            'warning' => 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-50',

            'danger' => 'border-red-200 bg-red-50 text-red-900 dark:border-red-900 dark:bg-red-950 dark:text-red-50',

            'info' => 'border-blue-200 bg-blue-50 text-blue-900 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-50',

            default => 'border-slate-200 bg-slate-50 text-slate-900 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100',
        };
    }
}