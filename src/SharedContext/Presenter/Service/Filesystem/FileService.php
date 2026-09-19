<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Service\Filesystem;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Websymphonie\SharedContext\Domain\Service\Filesystem\FileServiceInterface;

readonly class FileService implements FileServiceInterface
{
    public const string UPLOAD_DIR = '/public/uploads';

    public function __construct(private ParameterBagInterface $params)
    {
    }

    public function deleteFile(string $directory, ?string $filename): bool
    {
        if ($filename) {
            $filePath = sprintf("%s%s/$directory/$filename", $this->params->get('kernel.project_dir'), self::UPLOAD_DIR);

            if (file_exists($filePath)) {
                return unlink($filePath);
            }
        }

        return false;
    }

    public function moveFile(UploadedFile $file, string $targetDirectory, string $filename): void
    {
        $file->move(sprintf("%s%s/%s", $this->params->get('kernel.project_dir'), self::UPLOAD_DIR, $targetDirectory), $filename);
    }
}