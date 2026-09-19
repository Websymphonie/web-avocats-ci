<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Application\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Websymphonie\MediaContext\Domain\Model\StoredFile;

interface StoredFileStorageInterface
{
    public function store(UploadedFile $file): StoredFileStorageResult;
    public function delete(StoredFile $file): void;
    public function locate(StoredFile $file): string;
}
