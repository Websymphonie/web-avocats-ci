<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Component\Event;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\ContentContext\Domain\Model\Event;

#[AsTwigComponent('EventTableDropdown', template: 'content/admin/event/components/table_dropdown_component.html.twig')]
final class EventTableDropdownComponents
{
    public Event $event;
    public string $instanceId;
    public function mount(Event $event, string $instanceId = ''): void { $this->event = $event; $this->instanceId = $instanceId !== '' ? $instanceId : 'event-' . $event->id; }
}
