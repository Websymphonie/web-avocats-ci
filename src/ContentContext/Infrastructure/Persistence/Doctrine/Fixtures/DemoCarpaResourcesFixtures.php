<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Fixtures;

use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Websymphonie\ContentContext\Domain\Enum\DocumentAccessLevel;
use Websymphonie\ContentContext\Domain\Enum\DocumentStatus;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\DocumentPublication\DocumentPublicationEntity;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\MediaContext\Application\Service\StoredFileUploadServiceInterface;

final class DemoCarpaResourcesFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public function __construct(private readonly StoredFileUploadServiceInterface $files)
    {
    }

    public static function getGroups(): array
    {
        return ['demo'];
    }

    public function getDependencies(): array
    {
        return [DemoContentFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        DbLogListener::withoutLogging(function () use ($manager): void {
            $slug = 'reglement-interieur-barreau-cote-ivoire';
            $publication = $manager->getRepository(DocumentPublicationEntity::class)->findOneBy(['slug' => $slug]);

            if (!$publication instanceof DocumentPublicationEntity) {
                $filename = 'reglement-interieur-barreau-cote-ivoire.pdf';
                $path = __DIR__ . '/Files/Carpa/' . $filename;
                if (!is_file($path) || !is_readable($path)) {
                    throw new \RuntimeException(sprintf('Asset documentaire de fixture introuvable ou illisible : %s', $path));
                }

                $temporaryPath = tempnam(sys_get_temp_dir(), 'carpa-resource-');
                if ($temporaryPath === false || !copy($path, $temporaryPath)) {
                    if ($temporaryPath !== false && is_file($temporaryPath)) {
                        unlink($temporaryPath);
                    }

                    throw new \RuntimeException('Impossible de préparer temporairement un asset documentaire de fixture.');
                }

                try {
                    $storedFile = $this->files->upload(new UploadedFile($temporaryPath, $filename, 'application/pdf', null, true));
                } finally {
                    if (is_file($temporaryPath)) {
                        unlink($temporaryPath);
                    }
                }

                $publication = (new DocumentPublicationEntity())->setStoredFileId($storedFile->id);
            }

            $publication->setTitle('Règlement intérieur du Barreau de Côte d’Ivoire')
                ->setSlug($slug)
                ->setDescription('Texte institutionnel général du Barreau de Côte d’Ivoire, comportant une section relative aux règlements pécuniaires.')
                ->setAccessLevel(DocumentAccessLevel::PUBLIC)
                ->setStatus(DocumentStatus::PUBLISHED)
                ->setPublishedAt($publication->getPublishedAt() ?? new DateTimeImmutable())
                ->replaceTags([]);

            $manager->persist($publication);
            $manager->flush();
        });
    }
}
