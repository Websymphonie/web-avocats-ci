<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Bootstrap;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Domain\Enum\DocumentAccessLevel;
use Websymphonie\ContentContext\Domain\Enum\DocumentStatus;
use Websymphonie\ContentContext\Domain\Enum\PageStatus;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Batonnier\BatonnierMandateEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\CouncilMember\CouncilMemberEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\DocumentPublication\DocumentPublicationEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Page\PageEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\PagePerson\PagePersonEntryEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\PagePerson\PagePersonGroupEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Tag\TagEntity;
use Websymphonie\ContentContext\Infrastructure\SeedData\InstitutionalDocumentData;
use Websymphonie\MediaContext\Application\Service\StoredFileStorageInterface;
use Websymphonie\MediaContext\Application\Service\StoredFileUploadServiceInterface;
use Websymphonie\MediaContext\Domain\Exception\InvalidStoredFileException;
use Websymphonie\MediaContext\Domain\Exception\StoredFileNotFoundException;
use Websymphonie\MediaContext\Domain\Repository\StoredFileRepositoryInterface;
use Websymphonie\ContentContext\Infrastructure\SeedData\InstitutionalPageContent;

final readonly class InstitutionalContentBootstrapper
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RichTextSanitizerInterface $sanitizer,
        private StoredFileUploadServiceInterface $storedFiles,
        private StoredFileStorageInterface $storedFileStorage,
        private StoredFileRepositoryInterface $storedFileRepository,
    ) {
    }

    /** @return array{pages: int, batonnier: int, councilMembers: int, personGroups: int, personEntries: int, documents: int, documentConflicts: list<string>} */
    public function bootstrap(): array
    {
        $contents = InstitutionalPageContent::load($this->sanitizer);
        $createdPages = 0;

        foreach (InstitutionalPageContent::definitions() as $definition) {
            $group = $definition['group'];
            $page = $this->entityManager->getRepository(PageEntity::class)->findOneBy([
                'editorialGroup' => $group,
                'slug' => $definition['slug'],
            ]);
            if (!$page instanceof PageEntity) {
                $page = new PageEntity();
                ++$createdPages;
            }

            $page->setTitle($definition['title'])
                ->setSlug($definition['slug'])
                ->setContent($contents[$definition['contentKey']])
                ->setStatus($definition['status'])
                ->setGroup($group)
                ->setSortOrder($definition['sortOrder']);

            if ($definition['status'] === PageStatus::PUBLISHED && $page->getPublishedAt() === null) {
                $page->setPublishedAt(new DateTimeImmutable());
            } elseif ($definition['status'] === PageStatus::DRAFT) {
                $page->setPublishedAt(null);
            }

            $this->entityManager->persist($page);
        }

        $batonnierData = InstitutionalPageContent::currentBatonnier();
        $batonnier = $this->entityManager->getRepository(BatonnierMandateEntity::class)->findOneBy([
            'fullName' => $batonnierData['fullName'],
        ]);
        $createdBatonnier = 0;
        if (!$batonnier instanceof BatonnierMandateEntity) {
            $batonnier = new BatonnierMandateEntity();
            ++$createdBatonnier;
        }
        $batonnier->setFullName($batonnierData['fullName'])
            ->setMandateStartedAt(new DateTimeImmutable($batonnierData['startedAt']))
            ->setMandateEndedAt(null)
            ->setSummary($batonnierData['summary']);
        $this->entityManager->persist($batonnier);

        $createdMembers = 0;
        foreach (InstitutionalPageContent::councilMembers() as $index => $memberData) {
            $member = $this->entityManager->getRepository(CouncilMemberEntity::class)->findOneBy([
                'fullName' => $memberData['fullName'],
            ]);
            if (!$member instanceof CouncilMemberEntity) {
                $member = new CouncilMemberEntity();
                ++$createdMembers;
            }

            $member->setFullName($memberData['fullName'])
                ->setFunction($memberData['function'])
                ->setSortOrder(($index + 1) * 10)
                ->setMandateStartedAt(null)
                ->setMandateEndedAt(null);
            $this->entityManager->persist($member);
        }

        $this->entityManager->flush();
        $peopleResult = $this->bootstrapPagePersonGroups();
        $this->entityManager->flush();
        $documentResult = $this->bootstrapDocuments();

        return [
            'pages' => $createdPages,
            'batonnier' => $createdBatonnier,
            'councilMembers' => $createdMembers,
            'personGroups' => $peopleResult['groups'],
            'personEntries' => $peopleResult['entries'],
            'documents' => $documentResult['created'],
            'documentConflicts' => $documentResult['conflicts'],
        ];
    }

    /** @return array{groups: int, entries: int} */
    private function bootstrapPagePersonGroups(): array
    {
        $page = $this->entityManager->getRepository(PageEntity::class)->findOneBy([
            'editorialGroup' => \Websymphonie\ContentContext\Domain\Enum\PageGroup::BAR,
            'slug' => 'historique',
        ]);
        if (!$page instanceof PageEntity) {
            throw new \RuntimeException('La Page BAR « historique » est nécessaire pour installer les personnes institutionnelles.');
        }

        $createdGroups = 0;
        $createdEntries = 0;
        foreach (InstitutionalPageContent::pagePersonGroups() as $groupData) {
            $group = $this->entityManager->getRepository(PagePersonGroupEntity::class)->findOneBy([
                'page' => $page,
                'key' => $groupData['key'],
            ]);
            if (!$group instanceof PagePersonGroupEntity) {
                $group = (new PagePersonGroupEntity())->setPage($page)->setKey($groupData['key']);
                $page->addPersonGroup($group);
                $this->entityManager->persist($group);
                ++$createdGroups;
            }
            $group->setTitle($groupData['title'])->setSortOrder($groupData['sortOrder']);

            $expectedKeys = array_column($groupData['entries'], 'key');
            foreach ($group->getEntries()->toArray() as $existingEntry) {
                if (!in_array($existingEntry->getKey(), $expectedKeys, true)) {
                    $group->removeEntry($existingEntry);
                    $this->entityManager->remove($existingEntry);
                }
            }

            foreach ($groupData['entries'] as $entryData) {
                $entry = $this->entityManager->getRepository(PagePersonEntryEntity::class)->findOneBy([
                    'group' => $group,
                    'key' => $entryData['key'],
                ]);
                if (!$entry instanceof PagePersonEntryEntity) {
                    $entry = (new PagePersonEntryEntity())->setGroup($group)->setKey($entryData['key']);
                    $group->addEntry($entry);
                    $this->entityManager->persist($entry);
                    ++$createdEntries;
                }
                $entry->setDisplayName($entryData['displayName'])
                    ->setRoleLabel($entryData['roleLabel'])
                    ->setPeriodLabel($entryData['periodLabel'])
                    ->setSortOrder($entryData['sortOrder']);
            }
        }

        return ['groups' => $createdGroups, 'entries' => $createdEntries];
    }

    /** @return array{created: int, conflicts: list<string>} */
    private function bootstrapDocuments(): array
    {
        $tagRepository = $this->entityManager->getRepository(TagEntity::class);
        $documentRepository = $this->entityManager->getRepository(DocumentPublicationEntity::class);
        $definitions = InstitutionalDocumentData::definitions();
        $fundTagSlug = InstitutionalDocumentData::FUND_TAG_SLUG;
        $fundTag = $tagRepository->findOneBy(['slug' => $fundTagSlug]);
        $conflicts = [];
        $toCreate = [];

        foreach ($definitions as $definition) {
            $sourcePath = InstitutionalDocumentData::sourcePath($definition);
            if (!is_file($sourcePath) || !is_readable($sourcePath)) {
                throw new \RuntimeException(sprintf('Asset documentaire institutionnel introuvable ou illisible : %s', $sourcePath));
            }

            $publication = $documentRepository->findOneBy(['slug' => $definition['slug']]);
            if (!$publication instanceof DocumentPublicationEntity) {
                if ($definition['tagSlug'] !== null && $fundTag instanceof TagEntity && $fundTag->getName() !== $definition['tagName']) {
                    $conflicts[] = $definition['slug'];
                    continue;
                }
                $toCreate[] = $definition;
                continue;
            }

            if ($definition['tagSlug'] !== null && $fundTag instanceof TagEntity && $fundTag->getName() !== $definition['tagName']) {
                $conflicts[] = $definition['slug'];
                continue;
            }

            if (!$this->matchesCanonicalDocument($publication, $definition, $fundTag instanceof TagEntity ? $fundTag : null, $sourcePath)) {
                $conflicts[] = $definition['slug'];
            }
        }

        if ($toCreate !== [] && !$fundTag instanceof TagEntity && array_filter($toCreate, static fn (array $definition): bool => $definition['tagSlug'] === $fundTagSlug) !== []) {
            $fundTag = (new TagEntity())->setName(InstitutionalDocumentData::FUND_TAG_NAME)->setSlug($fundTagSlug);
            $this->entityManager->persist($fundTag);
            $this->entityManager->flush();
        }

        $created = 0;
        foreach ($toCreate as $definition) {
            $upload = InstitutionalDocumentData::copyToTemporaryUpload($definition, 'institutional-document-');
            try {
                $storedFile = $this->storedFiles->upload($upload);
            } finally {
                if (is_file($upload->getPathname())) {
                    unlink($upload->getPathname());
                }
            }

            $tags = $definition['tagSlug'] === null ? [] : [$fundTag];
            $publication = (new DocumentPublicationEntity())
                ->setTitle($definition['title'])
                ->setSlug($definition['slug'])
                ->setDescription($definition['description'])
                ->setStoredFileId($storedFile->id)
                ->setAccessLevel($definition['accessLevel'])
                ->setStatus(DocumentStatus::PUBLISHED)
                ->setPublishedAt(new DateTimeImmutable())
                ->replaceTags($tags);

            try {
                $this->entityManager->persist($publication);
                $this->entityManager->flush();
                ++$created;
            } catch (\Throwable $exception) {
                try {
                    $this->storedFiles->delete($storedFile);
                } catch (\Throwable) {
                    // Preserve the original persistence error; storage cleanup is best effort.
                }

                throw $exception;
            }
        }

        return ['created' => $created, 'conflicts' => array_values(array_unique($conflicts))];
    }

    /** @param array{title: string, slug: string, description: string, filename: string, directory: string, accessLevel: DocumentAccessLevel, tagSlug: ?string, tagName: ?string} $definition */
    private function matchesCanonicalDocument(DocumentPublicationEntity $publication, array $definition, ?TagEntity $fundTag, string $sourcePath): bool
    {
        if ($publication->getTitle() !== $definition['title']
            || $publication->getDescription() !== $definition['description']
            || $publication->getAccessLevel() !== $definition['accessLevel']
            || $publication->getStatus() !== DocumentStatus::PUBLISHED
            || $publication->getPublishedAt() === null) {
            return false;
        }

        $expectedTagSlugs = $definition['tagSlug'] === null ? [] : [$definition['tagSlug']];
        $actualTagSlugs = [];
        foreach ($publication->getTags() as $tag) {
            $actualTagSlugs[] = $tag->getSlug();
        }
        sort($actualTagSlugs);
        sort($expectedTagSlugs);
        if ($actualTagSlugs !== $expectedTagSlugs || ($expectedTagSlugs !== [] && !$fundTag instanceof TagEntity)) {
            return false;
        }

        try {
            $storedFile = $this->storedFileRepository->getById($publication->getStoredFileId());
        } catch (StoredFileNotFoundException) {
            return false;
        }

        $sourceChecksum = hash_file('sha256', $sourcePath);
        $sourceSize = filesize($sourcePath);
        try {
            $storedPath = $this->storedFileStorage->locate($storedFile);
            $storedChecksum = hash_file('sha256', $storedPath);
        } catch (StoredFileNotFoundException|InvalidStoredFileException) {
            return false;
        }

        return $sourceChecksum !== false
            && $sourceSize !== false
            && $storedChecksum !== false
            && hash_equals($sourceChecksum, $storedChecksum)
            && $storedFile->originalName === $definition['filename']
            && $storedFile->mimeType === 'application/pdf'
            && $storedFile->size === $sourceSize
            && hash_equals($sourceChecksum, $storedFile->checksum);
    }
}
