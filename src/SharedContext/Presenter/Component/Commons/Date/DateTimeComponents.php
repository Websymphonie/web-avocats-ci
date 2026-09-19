<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Component\Commons\Date;

use DateTimeInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('DateTimeComponent', template: 'shared/components/date/datetime_component.html.twig')]
class DateTimeComponents
{
    public DateTimeInterface $date;
    public ?string $format = "full";
    public ?string $formatTime = "none";

    public function mount(DateTimeInterface $date): void
    {
        $this->date = $date;
    }
}