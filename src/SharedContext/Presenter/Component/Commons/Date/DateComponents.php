<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Component\Commons\Date;

use DateTimeImmutable;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('DateComponent', template: 'shared/components/date/date_component.html.twig')]
class DateComponents
{
    public DateTimeImmutable $date;
    public ?string $format = "full";
    public ?string $formatTime = "none";
    public bool $timeOnly = false;

    public function mount(DateTimeImmutable $date): void
    {
        $this->date = $date;
    }
}