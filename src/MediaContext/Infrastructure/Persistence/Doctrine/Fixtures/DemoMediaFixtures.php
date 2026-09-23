<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Websymphonie\MediaContext\Application\Service\MediaUploadServiceInterface;
use Websymphonie\MediaContext\Domain\Model\Media;
use Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Entity\MediaEntity;

final class DemoMediaFixtures extends Fixture implements FixtureGroupInterface
{
    public function __construct(private readonly MediaUploadServiceInterface $mediaUpload)
    {
    }

    public static function getGroups(): array
    {
        return ['demo'];
    }

    public function load(ObjectManager $manager): void
    {
        $sources = [
            'content' => dirname(__DIR__, 6) . '/public/assets/logo.png',
            'content_alt' => dirname(__DIR__, 6) . '/public/assets/avatar.png',
            'content_bar_history' => dirname(__DIR__, 6) . '/public/assets/images/barreau-anciens-batonniers.png',
            'learning' => dirname(__DIR__, 6) . '/public/assets/logo.png',
            'learning_alt' => dirname(__DIR__, 6) . '/public/assets/avatar.png',
        ];

        foreach ($sources as $reference => $source) {
            if (!is_file($source)) {
                throw new \RuntimeException(sprintf('Asset de fixture introuvable : %s', $source));
            }

            $media = $this->uploadCopy($source, $reference, str_starts_with($reference, 'learning') ? 'training/covers' : 'content/covers');
            $entity = $manager->getRepository(MediaEntity::class)->find($media->id);
            if (!$entity instanceof MediaEntity) {
                throw new \RuntimeException(sprintf('Média de fixture introuvable après upload : %s', $reference));
            }
            $this->addReference('demo_media_' . $reference, $entity);
        }
    }

    private function uploadCopy(string $source, string $reference, string $storagePrefix): Media
    {
        $temporary = tempnam(sys_get_temp_dir(), 'avocat-fixture-');
        if ($temporary === false || !copy($source, $temporary)) {
            throw new \RuntimeException(sprintf('Impossible de préparer l’asset de fixture %s.', $reference));
        }

        try {
            return $this->mediaUpload->upload(new UploadedFile($temporary, $reference . '.png', 'image/png', null, true), $storagePrefix);
        } finally {
            if (is_file($temporary)) {
                @unlink($temporary);
            }
        }
    }
}
