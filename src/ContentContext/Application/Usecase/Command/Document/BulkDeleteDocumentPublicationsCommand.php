<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\Document;

final readonly class BulkDeleteDocumentPublicationsCommand
{
    /** @param list<int> $ids */
    public function __construct(public array $ids) {}
}
