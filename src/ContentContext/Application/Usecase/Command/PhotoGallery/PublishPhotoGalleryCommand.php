<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery;

final readonly class PublishPhotoGalleryCommand { public function __construct(public int $id) {} }
