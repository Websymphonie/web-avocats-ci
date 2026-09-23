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
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Tag\TagEntity;
use Websymphonie\ContentContext\Infrastructure\SeedData\InstitutionalDocumentData;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\MediaContext\Application\Service\StoredFileUploadServiceInterface;

final class DemoFundSolidarityResourcesFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
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
        return [DemoContentFixtures::class, DemoContentTaxonomyFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        DbLogListener::withoutLogging(function () use ($manager): void {
            $tag = $manager->getRepository(TagEntity::class)->findOneBy(['slug' => 'fonds-de-solidarite']);
            if (!$tag instanceof TagEntity) {
                throw new \LogicException('Le tag Fonds de Solidarité doit être chargé avant ses ressources.');
            }

            $publishedAt = new DateTimeImmutable();
            $documents = array_slice(InstitutionalDocumentData::definitions(), 1);

            foreach ($documents as $definition) {
                $publication = $manager->getRepository(DocumentPublicationEntity::class)->findOneBy(['slug' => $definition['slug']]);
                if (!$publication instanceof DocumentPublicationEntity) {
                    $upload = InstitutionalDocumentData::copyToTemporaryUpload($definition, 'fund-resource-');
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
                    ->setPublishedAt($publication->getPublishedAt() ?? $publishedAt)
                    ->replaceTags([$tag]);
                $manager->persist($publication);
            }

            $manager->flush();
        });
    }
}
