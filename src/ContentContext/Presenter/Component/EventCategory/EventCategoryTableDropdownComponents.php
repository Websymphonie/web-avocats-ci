<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Component\EventCategory;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\ContentContext\Domain\Model\EventCategory;

#[AsTwigComponent('EventCategoryTableDropdown', template: 'content/admin/event_category/components/table_dropdown_component.html.twig')]
final class EventCategoryTableDropdownComponents
{
    public EventCategory $category;
    public string $instanceId;
    public function mount(EventCategory $category, string $instanceId = ''): void { $this->category = $category; $this->instanceId = $instanceId !== '' ? $instanceId : 'event-category-' . $category->id; }
}
