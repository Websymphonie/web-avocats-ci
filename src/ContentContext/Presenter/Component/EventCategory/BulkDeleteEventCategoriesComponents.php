<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Component\EventCategory;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('BulkDeleteEventCategories', template: 'content/admin/event_category/components/bulk_delete_component.html.twig')]
final class BulkDeleteEventCategoriesComponents { public string $formId = 'event-category-bulk-delete'; }
