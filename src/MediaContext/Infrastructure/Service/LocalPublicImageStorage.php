<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Infrastructure\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Websymphonie\MediaContext\Application\Service\MediaStorageInterface;
use Websymphonie\MediaContext\Application\Service\StoredMediaFile;
use Websymphonie\MediaContext\Domain\Exception\InvalidMediaUploadException;
use Websymphonie\MediaContext\Domain\Model\Media;

/** Narrow, public-image-only local storage. Client filenames never form a storage path. */
final readonly class LocalPublicImageStorage implements MediaStorageInterface
{
    /** @param array<string, string> $extensions */
    public function __construct(private string $appStorageDir, private int $galleryMediaMaxSize, private Filesystem $filesystem, private array $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp']) {}

    public function store(UploadedFile $file, string $storagePrefix = 'galleries'): StoredMediaFile
    {
        if (!$file->isValid()) { throw new InvalidMediaUploadException('Le téléversement de l’image a échoué.'); }
        $size = $file->getSize();
        if ($size === false || $size < 1 || $size > $this->galleryMediaMaxSize) { throw new InvalidMediaUploadException(sprintf('Chaque image doit peser au plus %d Mio.', (int) ($this->galleryMediaMaxSize / 1024 / 1024))); }
        $mimeType = (string) $file->getMimeType();
        if (!isset($this->extensions[$mimeType])) { throw new InvalidMediaUploadException('Seules les images JPEG, PNG et WebP sont acceptées.'); }
        $imageInfo = @getimagesize($file->getPathname());
        if (!is_array($imageInfo) || (int) $imageInfo[0] < 1 || (int) $imageInfo[1] < 1) { throw new InvalidMediaUploadException('Le fichier envoyé n’est pas une image décodable.'); }
        if (!preg_match('/^(?:galleries|content\/covers|training\/covers)$/', $storagePrefix)) {
            throw new InvalidMediaUploadException('Le préfixe de stockage de l’image est invalide.');
        }
        $storageName = bin2hex(random_bytes(24)) . '.' . $this->extensions[$mimeType];
        $directory = $this->publicDirectory($storagePrefix);
        $this->filesystem->mkdir($directory, 0755);
        try { $file->move($directory, $storageName); } catch (\Throwable $exception) { throw new InvalidMediaUploadException('Impossible de stocker cette image.', previous: $exception); }
        return new StoredMediaFile((string) $file->getClientOriginalName(), $storageName, $mimeType, (int) $size, (int) $imageInfo[0], (int) $imageInfo[1], $storagePrefix . '/' . $storageName);
    }

    public function delete(Media $media): void
    {
        if (!preg_match('/^[a-f0-9]{48}\.(jpg|png|webp)$/', $media->storageName)) {
            throw new InvalidMediaUploadException('Le nom de stockage du média est invalide.');
        }
        $galleryPath = 'galleries/' . $media->storageName;
        $coverPath = 'content/covers/' . $media->storageName;
        $trainingCoverPath = 'training/covers/' . $media->storageName;
        if (!in_array($media->storagePath, [$galleryPath, $coverPath, $trainingCoverPath], true)) { throw new InvalidMediaUploadException('Le chemin de stockage du média est invalide.'); }
        $storagePrefix = match ($media->storagePath) {
            $galleryPath => 'galleries',
            $coverPath => 'content/covers',
            default => 'training/covers',
        };
        $path = $this->publicDirectory($storagePrefix) . '/' . $media->storageName;
        if ($this->filesystem->exists($path)) { $this->filesystem->remove($path); }
    }

    private function publicDirectory(string $storagePrefix): string
    {
        return rtrim($this->appStorageDir, '/') . '/public/' . $storagePrefix;
    }
}
