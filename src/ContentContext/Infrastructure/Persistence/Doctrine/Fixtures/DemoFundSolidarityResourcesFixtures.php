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
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Tag\TagEntity;
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

            $sourceDirectory = __DIR__ . '/Files/FundSolidarity';
            $publishedAt = new DateTimeImmutable();
            $documents = [
                ['Formulaire de demande de prêt', 'fonds-solidarite-demande-pret', 'Formulaire vierge à remplir pour une demande de prêt au Fonds de Solidarité.', 'fonds-solidarite-demande-pret.pdf'],
                ['Formulaire de demande de don', 'fonds-solidarite-demande-don', 'Formulaire vierge à remplir pour une demande de don au Fonds de Solidarité.', 'fonds-solidarite-demande-don.pdf'],
                ['Guide du réseau de soins', 'fonds-solidarite-guide-reseau-soins', 'Guide de l’assuré présentant le réseau de soins de la mutuelle santé du Barreau.', 'fonds-solidarite-guide-reseau-soins.pdf'],
            ];

            foreach ($documents as [$title, $slug, $description, $filename]) {
                $publication = $manager->getRepository(DocumentPublicationEntity::class)->findOneBy(['slug' => $slug]);
                if (!$publication instanceof DocumentPublicationEntity) {
                    $path = $sourceDirectory . '/' . $filename;
                    if (!is_file($path) || !is_readable($path)) {
                        throw new \RuntimeException(sprintf('Asset documentaire de fixture introuvable ou illisible : %s', $path));
                    }

                    $temporaryPath = tempnam(sys_get_temp_dir(), 'fund-resource-');
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

                $publication->setTitle($title)
                    ->setSlug($slug)
                    ->setDescription($description)
                    ->setAccessLevel(DocumentAccessLevel::LAWYER)
                    ->setStatus(DocumentStatus::PUBLISHED)
                    ->setPublishedAt($publication->getPublishedAt() ?? $publishedAt)
                    ->replaceTags([$tag]);
                $manager->persist($publication);
            }

            $manager->flush();
        });
    }
}
