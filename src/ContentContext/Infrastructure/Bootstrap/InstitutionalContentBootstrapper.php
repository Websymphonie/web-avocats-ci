<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Bootstrap;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Domain\Enum\PageStatus;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Batonnier\BatonnierMandateEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\CouncilMember\CouncilMemberEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Page\PageEntity;
use Websymphonie\ContentContext\Infrastructure\SeedData\InstitutionalPageContent;

final readonly class InstitutionalContentBootstrapper
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RichTextSanitizerInterface $sanitizer,
    ) {
    }

    /** @return array{pages: int, batonnier: int, councilMembers: int} */
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

        return [
            'pages' => $createdPages,
            'batonnier' => $createdBatonnier,
            'councilMembers' => $createdMembers,
        ];
    }
}
