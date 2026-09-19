<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class CreatePhotoGalleryCommand
{
    /**
     * @param list<int> $tags
     * @param list<UploadedFile> $images
     */
    public function __construct(public string $title = '', public string $description = '', public array $tags = [], public array $images = []) {}
}
