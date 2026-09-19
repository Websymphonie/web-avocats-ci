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
    public function __construct(private string $galleryMediaDirectory, private string $galleryMediaPrefix, private int $galleryMediaMaxSize, private Filesystem $filesystem, private array $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp']) {}

    public function store(UploadedFile $file): StoredMediaFile
    {
        if (!$file->isValid()) { throw new InvalidMediaUploadException('Le téléversement de l’image a échoué.'); }
        $size = $file->getSize();
        if ($size === false || $size < 1 || $size > $this->galleryMediaMaxSize) { throw new InvalidMediaUploadException(sprintf('Chaque image doit peser au plus %d Mio.', (int) ($this->galleryMediaMaxSize / 1024 / 1024))); }
        $mimeType = (string) $file->getMimeType();
        if (!isset($this->extensions[$mimeType])) { throw new InvalidMediaUploadException('Seules les images JPEG, PNG et WebP sont acceptées.'); }
        $imageInfo = @getimagesize($file->getPathname());
        if (!is_array($imageInfo) || (int) $imageInfo[0] < 1 || (int) $imageInfo[1] < 1) { throw new InvalidMediaUploadException('Le fichier envoyé n’est pas une image décodable.'); }
        $storageName = bin2hex(random_bytes(24)) . '.' . $this->extensions[$mimeType];
        $this->filesystem->mkdir($this->galleryMediaDirectory, 0755);
        try { $file->move($this->galleryMediaDirectory, $storageName); } catch (\Throwable $exception) { throw new InvalidMediaUploadException('Impossible de stocker cette image.', previous: $exception); }
        return new StoredMediaFile((string) $file->getClientOriginalName(), $storageName, $mimeType, (int) $size, (int) $imageInfo[0], (int) $imageInfo[1], trim($this->galleryMediaPrefix, '/') . '/' . $storageName);
    }

    public function delete(Media $media): void
    {
        $expectedPath = trim($this->galleryMediaPrefix, '/') . '/' . $media->storageName;
        if (!hash_equals($expectedPath, $media->storagePath)) { throw new InvalidMediaUploadException('Le chemin de stockage du média est invalide.'); }
        $path = rtrim($this->galleryMediaDirectory, '/') . '/' . $media->storageName;
        if ($this->filesystem->exists($path)) { $this->filesystem->remove($path); }
    }
}
