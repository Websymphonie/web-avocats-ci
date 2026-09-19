<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery;

final readonly class ArchivePhotoGalleryCommand { public function __construct(public int $id) {} }
