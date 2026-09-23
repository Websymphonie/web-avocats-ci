<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Fixtures;

use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Websymphonie\ContentContext\Domain\Enum\DocumentStatus;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\DocumentPublication\DocumentPublicationEntity;
use Websymphonie\ContentContext\Infrastructure\SeedData\InstitutionalDocumentData;
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
            $definition = InstitutionalDocumentData::definitions()[0];
            $publication = $manager->getRepository(DocumentPublicationEntity::class)->findOneBy(['slug' => $definition['slug']]);

            if (!$publication instanceof DocumentPublicationEntity) {
                $upload = InstitutionalDocumentData::copyToTemporaryUpload($definition, 'carpa-resource-');
                try {
                    $storedFile = $this->files->upload($upload);
                } finally {
                    if (is_file($upload->getPathname())) {
                        unlink($upload->getPathname());
                    }
                }

                $publication = (new DocumentPublicationEntity())->setStoredFileId($storedFile->id);
            }

            $publication->setTitle($definition['title'])
                ->setSlug($definition['slug'])
                ->setDescription($definition['description'])
                ->setAccessLevel($definition['accessLevel'])
                ->setStatus(DocumentStatus::PUBLISHED)
                ->setPublishedAt($publication->getPublishedAt() ?? new DateTimeImmutable())
                ->replaceTags([]);

            $manager->persist($publication);
            $manager->flush();
        });
    }
}
