<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Application\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Websymphonie\MediaContext\Domain\Model\Media;

interface MediaStorageInterface
{
    public function store(UploadedFile $file, string $storagePrefix = 'galleries'): StoredMediaFile;

    public function delete(Media $media): void;
}
