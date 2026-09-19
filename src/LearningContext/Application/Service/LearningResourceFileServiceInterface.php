<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Websymphonie\MediaContext\Domain\Model\StoredFile;

interface LearningResourceFileServiceInterface
{
    public function upload(UploadedFile $file): StoredFile;
    public function deleteIfOrphaned(int $storedFileId): void;
}
