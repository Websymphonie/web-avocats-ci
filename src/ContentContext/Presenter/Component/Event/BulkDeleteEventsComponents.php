<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Component\Event;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('BulkDeleteEvents', template: 'content/admin/event/components/bulk_delete_component.html.twig')]
final class BulkDeleteEventsComponents { public string $formId = 'event-bulk-delete'; }
