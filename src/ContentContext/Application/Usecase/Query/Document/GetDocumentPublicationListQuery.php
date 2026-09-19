<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Query\Document;

use Websymphonie\ContentContext\Domain\Enum\DocumentAccessLevel;
use Websymphonie\ContentContext\Domain\Enum\DocumentStatus;

final readonly class GetDocumentPublicationListQuery
{
    public function __construct(public ?string $search = null, public ?DocumentStatus $status = null, public ?DocumentAccessLevel $accessLevel = null, public ?int $tagId = null, public int $page = 1, public int $limit = 20) {}
}
