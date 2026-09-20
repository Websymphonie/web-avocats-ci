<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Component\Page;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('BulkDeletePages', template: 'content/admin/page/components/bulk_delete_component.html.twig')]
final class BulkDeletePagesComponent
{
    public string $formId = 'page-bulk-delete';
}
