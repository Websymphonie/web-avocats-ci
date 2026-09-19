<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery;

final readonly class SetPhotoGalleryCoverCommand { public function __construct(public int $id, public int $mediaId) {} }
