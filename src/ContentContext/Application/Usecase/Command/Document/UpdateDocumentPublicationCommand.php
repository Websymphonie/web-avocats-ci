<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\Document;

use Websymphonie\ContentContext\Domain\Enum\DocumentAccessLevel;

final class UpdateDocumentPublicationCommand
{
    /** @param list<int> $tags */
    public function __construct(public int $id, public string $title = '', public string $description = '', public DocumentAccessLevel $accessLevel = DocumentAccessLevel::PUBLIC, public array $tags = []) {}
}
