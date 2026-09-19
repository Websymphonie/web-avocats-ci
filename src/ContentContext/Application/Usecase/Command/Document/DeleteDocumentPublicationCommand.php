<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\Document;

final readonly class DeleteDocumentPublicationCommand { public function __construct(public int $id) {} }
