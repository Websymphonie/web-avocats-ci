<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Service\Filesystem;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileServiceInterface
{
    public function deleteFile(string $directory, ?string $filename): bool;

    public function moveFile(UploadedFile $file, string $targetDirectory, string $filename): void;
}