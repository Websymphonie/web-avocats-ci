<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\Document;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Websymphonie\ContentContext\Domain\Enum\DocumentAccessLevel;

final class CreateDocumentPublicationCommand
{
    /** @param list<int> $tags */
    public function __construct(public string $title = '', public string $description = '', public DocumentAccessLevel $accessLevel = DocumentAccessLevel::PUBLIC, public array $tags = [], public ?UploadedFile $file = null) {}
}
