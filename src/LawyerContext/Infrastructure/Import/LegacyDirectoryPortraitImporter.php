<?php

declare(strict_types=1);

namespace Websymphonie\LawyerContext\Infrastructure\Import;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\LawyerProfile\LawyerProfileEntity;
use Websymphonie\MediaContext\Application\Service\MediaUploadServiceInterface;
use Websymphonie\MediaContext\Domain\Model\Media;
use Websymphonie\MediaContext\Domain\Repository\MediaRepositoryInterface;

/**
 * @phpstan-type PortraitSourceEntry array{displayName: string, portraitSourceUrl: ?string}
 * @phpstan-type PortraitManifestEntry array{sourceUrl: string, localFilename: string, sha256: string, mimeType: string, mediaId: int, mediaUuid: string}
 * @phpstan-type PortraitAsset array{path: string, sha256: string, mimeType: string, extension: string}
 */
final readonly class LegacyDirectoryPortraitImporter
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MediaUploadServiceInterface $mediaUpload,
        private MediaRepositoryInterface $mediaRepository,
        private LegacyPortraitDownloader $downloader,
        private Filesystem $filesystem,
        private int $galleryMediaMaxSize,
    ) {
    }

    /** @return array<string, mixed> */
    public function run(string $datasetPath, string $assetsDirectory, string $manifestPath, bool $write): array
    {
        $dataset = $this->readJson($datasetPath);
        if (!isset($dataset['entries']) || !is_array($dataset['entries']) || !array_is_list($dataset['entries'])) {
            throw new \InvalidArgumentException('Le dataset avocat doit contenir une liste entries.');
        }
        $entries = $this->validateSourceEntries($dataset['entries']);
        $manifest = $this->readManifest($manifestPath);
        $profiles = $this->loadImportedProfiles();
        $report = [
            'generatedAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'mode' => $write ? 'write' : 'dry-run',
            'dataset' => $datasetPath,
            'sourcePortraits' => 0,
            'profilesWithoutPortraitSource' => 0,
            'downloadPlanned' => 0,
            'downloadAttempted' => 0,
            'downloadSucceeded' => 0,
            'downloadFailed' => 0,
            'invalidImage' => 0,
            'mediaCreated' => 0,
            'mediaPlanned' => 0,
            'mediaUnchanged' => 0,
            'mediaConflicts' => 0,
            'profilesAssociated' => 0,
            'profilesAlreadyAssociated' => 0,
            'profilesMissing' => 0,
            'errors' => [],
        ];

        foreach ($entries as $sourceUuid => $entry) {
            $url = $entry['portraitSourceUrl'];
            if ($url === null) {
                $report['profilesWithoutPortraitSource']++;
                continue;
            }
            $report['sourcePortraits']++;
            $profile = $profiles[$sourceUuid] ?? null;
            if (!$profile instanceof LawyerProfileEntity) {
                $report['profilesMissing']++;
                $this->addError($report, $sourceUuid, $entry, 'profile_lookup', 'Profil absent de la base ; aucune création de LawyerProfile n’est effectuée.');
                continue;
            }

            $manifestEntry = $manifest[$sourceUuid] ?? null;
            $profileMediaId = $profile->getPortraitMediaId();
            if ($profileMediaId !== null && !$this->profileMatchesManifest($profileMediaId, $manifestEntry)) {
                $report['mediaConflicts']++;
                $this->addError($report, $sourceUuid, $entry, 'association', 'Un portrait déjà associé ne correspond pas à la provenance de cet import.');
                continue;
            }

            if ($manifestEntry !== null) {
                $media = $this->findManifestMedia($manifestEntry, $sourceUuid, $entry, $report);
                if ($media === null) {
                    continue;
                }
                $asset = $this->ensureSourceAsset($sourceUuid, $entry, $assetsDirectory, $manifestEntry, $write, $report);
                if ($asset === null) {
                    continue;
                }
                if ($profileMediaId === $media->id) {
                    $report['mediaUnchanged']++;
                    $report['profilesAlreadyAssociated']++;
                    continue;
                }
                if (!$write) {
                    $report['mediaUnchanged']++;
                    $report['profilesAssociated']++;
                    continue;
                }

                $profile->setPortraitMediaId($media->id);
                $this->entityManager->flush();
                $report['mediaUnchanged']++;
                $report['profilesAssociated']++;
                continue;
            }

            if ($profileMediaId !== null) {
                $report['mediaConflicts']++;
                $this->addError($report, $sourceUuid, $entry, 'association', 'Portrait existant sans provenance import vérifiable.');
                continue;
            }

            $asset = $this->ensureSourceAsset($sourceUuid, $entry, $assetsDirectory, null, $write, $report);
            if ($asset === null) {
                if (!$write) {
                    $report['mediaPlanned']++;
                }
                continue;
            }
            if (!$write) {
                $report['mediaPlanned']++;
                continue;
            }

            $media = $this->uploadAsset($asset['path'], $sourceUuid, $asset['mimeType'], $asset['extension']);
            $manifest[$sourceUuid] = [
                'sourceUrl' => $url,
                'localFilename' => basename($asset['path']),
                'sha256' => $asset['sha256'],
                'mimeType' => $asset['mimeType'],
                'mediaId' => $media->id,
                'mediaUuid' => $media->uuid,
            ];
            try {
                $this->writeManifest($manifestPath, $manifest);
            } catch (\Throwable $exception) {
                try {
                    $this->mediaUpload->delete($media);
                } catch (\Throwable) {
                    // Keep the original error. An unassociated orphan can be safely reviewed later.
                }
                throw $exception;
            }

            $profile->setPortraitMediaId($media->id);
            $this->entityManager->flush();
            $report['mediaCreated']++;
            $report['profilesAssociated']++;
        }

        $report['manifest'] = $manifestPath;
        $report['assetsDirectory'] = $assetsDirectory;

        return $report;
    }

    /**
     * @param PortraitSourceEntry $entry
     * @param PortraitManifestEntry|null $manifestEntry
     * @param array<string, mixed> $report
     * @return PortraitAsset|null
     */
    private function ensureSourceAsset(string $sourceUuid, array $entry, string $assetsDirectory, ?array $manifestEntry, bool $write, array &$report): ?array
    {
        $existingPaths = array_values(array_filter(
            array_map(fn (string $extension): string => $assetsDirectory . '/' . $sourceUuid . '.' . $extension, ['jpg', 'png', 'webp']),
            fn (string $path): bool => is_file($path),
        ));
        if (count($existingPaths) > 1) {
            $report['mediaConflicts']++;
            $this->addError($report, $sourceUuid, $entry, 'local_asset', 'Plusieurs extensions existent pour le même UUID source.');

            return null;
        }

        if ($existingPaths !== []) {
            $asset = $this->validateLocalAsset($existingPaths[0], $sourceUuid, $entry, $report);
            if ($asset === null) {
                return null;
            }
            if ($manifestEntry !== null && !hash_equals((string) $manifestEntry['sha256'], $asset['sha256'])) {
                $report['mediaConflicts']++;
                $this->addError($report, $sourceUuid, $entry, 'local_asset', 'La somme de contrôle locale ne correspond pas au manifeste.');

                return null;
            }

            return $asset;
        }

        if (!$write) {
            $report['downloadPlanned']++;

            return null;
        }

        $report['downloadAttempted']++;
        try {
            $download = $this->downloader->download($entry['portraitSourceUrl']);
        } catch (LegacyPortraitDownloadException $exception) {
            if ($exception->stage === 'invalid_image') {
                $report['invalidImage']++;
            } else {
                $report['downloadFailed']++;
            }
            $this->addError($report, $sourceUuid, $entry, $exception->stage, $exception->getMessage());

            return null;
        }

        $sha256 = hash('sha256', $download->contents);
        if ($manifestEntry !== null && !hash_equals((string) $manifestEntry['sha256'], $sha256)) {
            $report['mediaConflicts']++;
            $this->addError($report, $sourceUuid, $entry, 'source_checksum', 'Le portrait source a changé depuis le manifeste ; l’asset local n’est pas remplacé.');

            return null;
        }

        $this->filesystem->mkdir($assetsDirectory, 0755);
        $path = $assetsDirectory . '/' . $sourceUuid . '.' . $download->extension;
        $temporaryPath = tempnam($assetsDirectory, '.portrait-');
        if ($temporaryPath === false) {
            throw new \RuntimeException('Impossible de créer un fichier temporaire pour le portrait.');
        }
        try {
            if (file_put_contents($temporaryPath, $download->contents, LOCK_EX) === false || !rename($temporaryPath, $path)) {
                throw new \RuntimeException('Impossible d’enregistrer l’asset source du portrait.');
            }
        } finally {
            if (is_file($temporaryPath)) {
                unlink($temporaryPath);
            }
        }
        $report['downloadSucceeded']++;

        return ['path' => $path, 'sha256' => $sha256, 'mimeType' => $download->mimeType, 'extension' => $download->extension];
    }

    /**
     * @param PortraitSourceEntry $entry
     * @param array<string, mixed> $report
     * @return PortraitAsset|null
     */
    private function validateLocalAsset(string $path, string $sourceUuid, array $entry, array &$report): ?array
    {
        $size = filesize($path);
        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        $imageInfo = @getimagesize($path);
        $formats = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if ($size === false || $size < 1 || $size > $this->galleryMediaMaxSize
            || !is_string($mimeType) || !isset($formats[$mimeType])
            || pathinfo($path, PATHINFO_EXTENSION) !== $formats[$mimeType]
            || !is_array($imageInfo) || $imageInfo[0] < 1 || $imageInfo[1] < 1
            || image_type_to_mime_type($imageInfo[2]) !== $mimeType) {
            $report['invalidImage']++;
            $this->addError($report, $sourceUuid, $entry, 'local_asset', 'L’asset source local est vide, trop volumineux ou n’est pas une image décodable supportée.');

            return null;
        }

        return ['path' => $path, 'sha256' => hash_file('sha256', $path) ?: '', 'mimeType' => $mimeType, 'extension' => $formats[$mimeType]];
    }

    private function uploadAsset(string $sourcePath, string $sourceUuid, string $mimeType, string $extension): Media
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'lawyer-portrait-');
        if ($temporaryPath === false || !copy($sourcePath, $temporaryPath)) {
            throw new \RuntimeException('Impossible de préparer une copie temporaire de l’asset source.');
        }
        try {
            return $this->mediaUpload->upload(new UploadedFile($temporaryPath, $sourceUuid . '.' . $extension, $mimeType, null, true), 'institution/lawyers');
        } finally {
            if (is_file($temporaryPath)) {
                unlink($temporaryPath);
            }
        }
    }

    /** @param PortraitManifestEntry|null $manifestEntry */
    private function profileMatchesManifest(int $profileMediaId, ?array $manifestEntry): bool
    {
        if ($manifestEntry === null || (int) $manifestEntry['mediaId'] !== $profileMediaId) {
            return false;
        }

        try {
            $media = $this->mediaRepository->getById($profileMediaId);
        } catch (\Throwable) {
            return false;
        }

        return $media->uuid === $manifestEntry['mediaUuid'] && $media->storagePath === 'institution/lawyers/' . $media->storageName;
    }

    /**
     * @param PortraitManifestEntry $manifestEntry
     * @param PortraitSourceEntry $entry
     * @param array<string, mixed> $report
     */
    private function findManifestMedia(array $manifestEntry, string $sourceUuid, array $entry, array &$report): ?Media
    {
        try {
            $media = $this->mediaRepository->getById((int) $manifestEntry['mediaId']);
        } catch (\Throwable) {
            $report['mediaConflicts']++;
            $this->addError($report, $sourceUuid, $entry, 'media_lookup', 'Le média du manifeste est absent de la base.');

            return null;
        }

        if ($media->uuid !== $manifestEntry['mediaUuid'] || $media->storagePath !== 'institution/lawyers/' . $media->storageName) {
            $report['mediaConflicts']++;
            $this->addError($report, $sourceUuid, $entry, 'media_lookup', 'Le média lié au manifeste ne correspond pas à un portrait LawyerProfile.');

            return null;
        }

        return $media;
    }

    /** @return array<string, LawyerProfileEntity> */
    private function loadImportedProfiles(): array
    {
        $profiles = $this->entityManager->createQueryBuilder()
            ->select('profile')
            ->from(LawyerProfileEntity::class, 'profile')
            ->where('profile.legacySourceUuid IS NOT NULL')
            ->getQuery()
            ->getResult();
        $indexed = [];
        foreach ($profiles as $profile) {
            if ($profile instanceof LawyerProfileEntity && $profile->getLegacySourceUuid() !== null) {
                $indexed[$profile->getLegacySourceUuid()->toRfc4122()] = $profile;
            }
        }

        return $indexed;
    }

    /** @param list<mixed> $entries
     *  @return array<string, PortraitSourceEntry>
     */
    private function validateSourceEntries(array $entries): array
    {
        $validated = [];
        foreach ($entries as $index => $entry) {
            if (!is_array($entry) || !is_string($entry['sourceUuid'] ?? null) || !Uuid::isValid($entry['sourceUuid'])) {
                throw new \InvalidArgumentException(sprintf('UUID source invalide à l’entrée %d.', $index + 1));
            }
            $sourceUuid = Uuid::fromString($entry['sourceUuid'])->toRfc4122();
            if (isset($validated[$sourceUuid])) {
                throw new \InvalidArgumentException(sprintf('UUID source dupliqué dans le dataset : %s.', $sourceUuid));
            }
            $url = $entry['portraitSourceUrl'] ?? null;
            if ($url !== null && !is_string($url)) {
                throw new \InvalidArgumentException(sprintf('URL portrait invalide à l’entrée %d.', $index + 1));
            }
            $validated[$sourceUuid] = [
                'displayName' => is_string($entry['displayName'] ?? null) ? $entry['displayName'] : $sourceUuid,
                'portraitSourceUrl' => is_string($url) && trim($url) !== '' ? trim($url) : null,
            ];
        }

        return $validated;
    }

    /** @return array<string, mixed> */
    private function readJson(string $path): array
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new \InvalidArgumentException(sprintf('Dataset JSON absent ou illisible : %s', $path));
        }
        $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            throw new \InvalidArgumentException('Le dataset doit être un objet JSON.');
        }

        return $decoded;
    }

    /** @return array<string, PortraitManifestEntry> */
    private function readManifest(string $path): array
    {
        if (!is_file($path)) {
            return [];
        }
        $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded) || !is_array($decoded['entries'] ?? null)) {
            throw new \InvalidArgumentException('Le manifeste portrait est invalide ; import interrompu sans écriture.');
        }
        $manifest = [];
        foreach ($decoded['entries'] as $sourceUuid => $entry) {
            if (!is_string($sourceUuid) || !Uuid::isValid($sourceUuid) || !is_array($entry)
                || !is_string($entry['sourceUrl'] ?? null) || !is_string($entry['localFilename'] ?? null)
                || !preg_match('/^[a-f0-9]{64}$/', (string) ($entry['sha256'] ?? ''))
                || !is_string($entry['mimeType'] ?? null) || !is_numeric($entry['mediaId'] ?? null)
                || !is_string($entry['mediaUuid'] ?? null)) {
                throw new \InvalidArgumentException('Une entrée du manifeste portrait est invalide ; import interrompu.');
            }
            $manifest[Uuid::fromString($sourceUuid)->toRfc4122()] = [
                'sourceUrl' => $entry['sourceUrl'],
                'localFilename' => $entry['localFilename'],
                'sha256' => $entry['sha256'],
                'mimeType' => $entry['mimeType'],
                'mediaId' => (int) $entry['mediaId'],
                'mediaUuid' => $entry['mediaUuid'],
            ];
        }

        return $manifest;
    }

    /** @param array<string, PortraitManifestEntry> $manifest */
    private function writeManifest(string $path, array $manifest): void
    {
        $this->filesystem->mkdir(dirname($path), 0755);
        $temporaryPath = tempnam(dirname($path), '.manifest-');
        if ($temporaryPath === false) {
            throw new \RuntimeException('Impossible de créer le manifeste temporaire des portraits.');
        }
        try {
            $json = json_encode(['version' => 1, 'entries' => $manifest], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . PHP_EOL;
            if (file_put_contents($temporaryPath, $json, LOCK_EX) === false || !rename($temporaryPath, $path)) {
                throw new \RuntimeException('Impossible d’écrire le manifeste des portraits.');
            }
        } finally {
            if (is_file($temporaryPath)) {
                unlink($temporaryPath);
            }
        }
    }

    /** @param array<string, mixed> $report
     *  @param PortraitSourceEntry $entry
     */
    private function addError(array &$report, string $sourceUuid, array $entry, string $stage, string $reason): void
    {
        $report['errors'][] = [
            'legacySourceUuid' => $sourceUuid,
            'displayName' => $entry['displayName'],
            'sourceUrl' => $entry['portraitSourceUrl'],
            'stage' => $stage,
            'reason' => $reason,
        ];
    }
}
