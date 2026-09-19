<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class AddPhotoGalleryImagesCommand
{
    /** @param list<UploadedFile> $images */
    public function __construct(public int $id, public array $images) {}
}
