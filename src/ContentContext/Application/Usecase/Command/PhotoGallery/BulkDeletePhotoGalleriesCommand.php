<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery;

final readonly class BulkDeletePhotoGalleriesCommand { /** @param list<int> $ids */ public function __construct(public array $ids) {} }
