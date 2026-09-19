<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Infrastructure\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Websymphonie\MediaContext\Application\Service\StoredFileStorageInterface;
use Websymphonie\MediaContext\Application\Service\StoredFileStorageResult;
use Websymphonie\MediaContext\Domain\Exception\InvalidStoredFileException;
use Websymphonie\MediaContext\Domain\Exception\StoredFileNotFoundException;
use Websymphonie\MediaContext\Domain\Model\StoredFile;

final readonly class PrivateDocumentStorage implements StoredFileStorageInterface
{
    /** @var array<string, string> */
    private const ALLOWED = [
        'application/pdf' => 'pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
    ];

    public function __construct(private string $appStorageDir, private int $documentMaxSize, private Filesystem $filesystem) {}

    public function store(UploadedFile $file): StoredFileStorageResult
    {
        if (!$file->isValid()) { throw new InvalidStoredFileException('Le téléversement du document a échoué.'); }
        $size = $file->getSize();
        $originalName = trim((string) $file->getClientOriginalName());
        $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
        $mimeType = (string) (new \finfo(FILEINFO_MIME_TYPE))->file($file->getPathname());
        if ($size === false || $size < 1 || $size > $this->documentMaxSize) { throw new InvalidStoredFileException(sprintf('Le document doit peser au plus %d Mio.', (int) ($this->documentMaxSize / 1024 / 1024))); }
        if (!isset(self::ALLOWED[$mimeType]) || self::ALLOWED[$mimeType] !== $extension) { throw new InvalidStoredFileException('Seuls les fichiers PDF, DOCX, XLSX et PPTX valides sont acceptés.'); }
        if (!is_readable($file->getPathname())) { throw new InvalidStoredFileException('Le document envoyé est illisible.'); }
        $fileName = bin2hex(random_bytes(24)) . '.' . self::ALLOWED[$mimeType];
        $directory = $this->documentDirectory();
        $this->filesystem->mkdir($directory, 0750);
        try { $file->move($directory, $fileName); } catch (\Throwable $exception) { throw new InvalidStoredFileException('Impossible de stocker ce document.', previous: $exception); }
        $storageName = 'documents/' . $fileName;
        $path = $directory . '/' . $fileName;
        $checksum = hash_file('sha256', $path);
        if ($checksum === false) { $this->filesystem->remove($path); throw new InvalidStoredFileException('Impossible de calculer l’empreinte du document.'); }
        return new StoredFileStorageResult($originalName !== '' ? $originalName : $storageName, $storageName, $mimeType, (int) $size, $checksum);
    }

    public function delete(StoredFile $file): void
    {
        $fileName = $this->fileName($file->storageName);
        if ($fileName === null) { throw new InvalidStoredFileException('Le nom de stockage du document est invalide.'); }
        $path = $this->documentDirectory() . '/' . $fileName;
        if ($this->filesystem->exists($path)) { $this->filesystem->remove($path); }
    }

    public function locate(StoredFile $file): string
    {
        $fileName = $this->fileName($file->storageName);
        if ($fileName === null) { throw new StoredFileNotFoundException(); }
        $path = $this->documentDirectory() . '/' . $fileName;
        if (!is_file($path) || !is_readable($path)) { throw new StoredFileNotFoundException('Le fichier physique du document est indisponible.'); }
        return $path;
    }

    private function documentDirectory(): string
    {
        return rtrim($this->appStorageDir, '/') . '/private/documents';
    }

    private function fileName(string $storageName): ?string
    {
        if (!preg_match('/^documents\/([a-f0-9]{48}\.(pdf|docx|xlsx|pptx))$/', $storageName, $matches)) {
            return null;
        }

        return $matches[1];
    }
}
