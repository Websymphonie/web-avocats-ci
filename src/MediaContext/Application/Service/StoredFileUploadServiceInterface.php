<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Application\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Websymphonie\MediaContext\Domain\Model\StoredFile;

interface StoredFileUploadServiceInterface
{
    public function upload(UploadedFile $file): StoredFile;
    public function delete(StoredFile $file): void;
}
