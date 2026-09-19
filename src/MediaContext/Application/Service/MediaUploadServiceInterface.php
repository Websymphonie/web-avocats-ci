<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Application\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Websymphonie\MediaContext\Domain\Model\Media;

interface MediaUploadServiceInterface
{
    public function upload(UploadedFile $file, string $storagePrefix = 'galleries'): Media;

    /** Deletes an unreferenced media and attempts best-effort physical cleanup. */
    public function delete(Media $media): void;
}
