<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery;

final readonly class ReorderPhotoGalleryItemsCommand { /** @param list<int> $mediaIds */ public function __construct(public int $id, public array $mediaIds) {} }
