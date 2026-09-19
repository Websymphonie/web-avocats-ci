<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Component\News;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('BulkDeleteNews', template: 'content/admin/news/components/bulk_delete_news_component.html.twig')]
final class BulkDeleteNewsComponents
{
    public string $formId = 'news-bulk-delete';
}
