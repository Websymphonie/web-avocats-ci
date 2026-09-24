<?php

declare(strict_types=1);

namespace Websymphonie\WebContext\Presenter\Controller\Page;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Websymphonie\ContentContext\Application\Model\PublishedPage;
use Websymphonie\ContentContext\Application\Service\DocumentDownloadPolicy;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Application\Usecase\Query\Page\FindPublishedPagesByGroupQuery;
use Websymphonie\ContentContext\Application\Usecase\Query\Page\FindPublishedPageBySlugQuery;
use Websymphonie\ContentContext\Application\Usecase\Query\Page\GetPagePersonGroupsQuery;
use Websymphonie\ContentContext\Application\Usecase\Query\Batonnier\GetCurrentBatonnierMandateQuery;
use Websymphonie\ContentContext\Application\Usecase\Query\CouncilMember\GetCurrentCouncilMembersQuery;
use Websymphonie\ContentContext\Domain\Enum\PageGroup;
use Websymphonie\ContentContext\Domain\Model\BatonnierMandate;
use Websymphonie\ContentContext\Domain\Model\CouncilMember;
use Websymphonie\ContentContext\Domain\Model\PagePersonGroup;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

final class GetPublicPageDetailsController extends AbstractController
{
    public function __construct(
        private readonly RichTextSanitizerInterface $sanitizer,
        private readonly MediaPublicUrlResolverInterface $mediaUrls,
        private readonly DocumentDownloadPolicy $documentDownloadPolicy,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/informations/{slug}', name: 'web_information_detail', requirements: ['slug' => '[a-z0-9]+(?:-[a-z0-9]+)*'], methods: ['GET'])]
    public function __invoke(string $slug): Response
    {
        $page = $this->findPublishedPage($slug);
        if ($page === null) {
            throw $this->createNotFoundException();
        }

        if ($slug === 'lbc-ft-fp' && $page->group === PageGroup::LBC) {
            return $this->redirectToRoute('web_lbc_ft_fp', status: Response::HTTP_MOVED_PERMANENTLY);
        }

        if ($slug === 'assistance-violences-domestiques' && $page->group === null) {
            return $this->redirectToRoute('web_domestic_violence_assistance', status: Response::HTTP_MOVED_PERMANENTLY);
        }

        if ($page->group === PageGroup::BAR) {
            return $this->redirectToRoute('web_bar_page_detail', ['slug' => $slug], Response::HTTP_MOVED_PERMANENTLY);
        }
        if ($page->group === PageGroup::PROFESSION) {
            return $this->redirectToRoute('web_profession_become_lawyer', status: Response::HTTP_MOVED_PERMANENTLY);
        }

        return $this->renderPage($page);
    }

    #[Route('/le-barreau/{slug}', name: 'web_bar_page_detail', requirements: ['slug' => '[a-z0-9]+(?:-[a-z0-9]+)*'], methods: ['GET'])]
    public function barPage(string $slug): Response
    {
        if ($slug === 'lbc-ft-fp') {
            return $this->redirectToRoute('web_lbc_ft_fp', status: Response::HTTP_MOVED_PERMANENTLY);
        }

        $page = $this->findPublishedPage($slug, PageGroup::BAR);
        if ($page === null || $page->group !== PageGroup::BAR) {
            throw $this->createNotFoundException();
        }

        $mandate = $slug === 'le-batonnier' ? $this->handleQuery(new GetCurrentBatonnierMandateQuery()) : null;
        $portraitUrl = $mandate?->portraitMediaId !== null
            ? $this->mediaUrls->resolveMany([$mandate->portraitMediaId])[$mandate->portraitMediaId] ?? null
            : null;

        $councilMembers = $slug === 'conseil-de-l-ordre' ? $this->handleQuery(new GetCurrentCouncilMembersQuery()) : [];
        $councilPortraitUrls = $this->resolveCouncilPortraits($councilMembers);
        $personGroups = $slug === 'historique' ? $this->handleQuery(new GetPagePersonGroupsQuery($page->id)) : [];
        $personPortraitUrls = $this->resolvePagePersonPortraits($personGroups);

        return $this->renderPage($page, $mandate, $portraitUrl, $councilMembers, $councilPortraitUrls, $personGroups, $personPortraitUrls);
    }

    #[Route('/lbc-ft-fp', name: 'web_lbc_ft_fp', methods: ['GET'])]
    public function lbcPage(): Response
    {
        $page = $this->findPublishedPage('lbc-ft-fp', PageGroup::LBC);
        if ($page === null) {
            throw $this->createNotFoundException();
        }

        return $this->renderPage($page);
    }

    #[Route('/assistance-violences-domestiques', name: 'web_domestic_violence_assistance', methods: ['GET'])]
    public function domesticViolenceAssistancePage(): Response
    {
        $page = $this->findPublishedPage('assistance-violences-domestiques');
        if ($page === null || $page->group !== null) {
            throw $this->createNotFoundException();
        }

        return $this->renderPage($page);
    }

    #[Route('/devenir-avocat', name: 'web_profession_become_lawyer', methods: ['GET'])]
    public function becomeLawyerPage(): Response
    {
        $page = $this->findPublishedPage('devenir-avocat', PageGroup::PROFESSION);
        if ($page === null) {
            throw $this->createNotFoundException();
        }

        return $this->renderPage($page);
    }

    #[Route('/carpa/{slug}', name: 'web_carpa_page_detail', requirements: ['slug' => '[a-z0-9]+(?:-[a-z0-9]+)*'], methods: ['GET'])]
    public function carpaPage(string $slug): Response
    {
        $page = $this->findPublishedPage($slug, PageGroup::CARPA);
        if ($page === null) {
            throw $this->createNotFoundException();
        }

        return $this->renderPage($page);
    }

    #[Route('/le-barreau/carpa', name: 'web_bar_legacy_carpa', priority: 10, methods: ['GET'])]
    public function legacyCarpa(): Response
    {
        return $this->redirectToRoute('web_carpa_page_detail', ['slug' => 'presentation'], Response::HTTP_MOVED_PERMANENTLY);
    }

    private function findPublishedPage(string $slug, ?PageGroup $group = null): ?PublishedPage
    {
        /** @var PublishedPage|null $page */
        $page = $this->handleQuery(new FindPublishedPageBySlugQuery($slug, $group));

        return $page;
    }

    /**
     * @param list<CouncilMember> $councilMembers
     * @param array<int, string> $councilPortraitUrls
     * @param list<PagePersonGroup> $personGroups
     * @param array<int, string> $personPortraitUrls
     */
    private function renderPage(PublishedPage $page, ?BatonnierMandate $mandate = null, ?string $portraitUrl = null, array $councilMembers = [], array $councilPortraitUrls = [], array $personGroups = [], array $personPortraitUrls = []): Response
    {
        $coverUrl = null;
        if ($page->coverMediaId !== null) {
            $coverUrl = $this->mediaUrls->resolveMany([$page->coverMediaId])[$page->coverMediaId] ?? null;
        }

        return $this->render('web/pages/show.html.twig', [
            'page' => $page,
            'coverUrl' => $coverUrl,
            'safeContent' => $this->sanitizer->sanitize($page->content),
            'contextualPages' => $this->findContextualPages($page),
            'mandate' => $mandate,
            'portraitUrl' => $portraitUrl,
            'councilMembers' => $councilMembers,
            'councilPortraitUrls' => $councilPortraitUrls,
            'personGroups' => $personGroups,
            'personPortraitUrls' => $personPortraitUrls,
            'canAccessFundResources' => $page->slug === 'fonds-de-solidarite' && $this->documentDownloadPolicy->canAccessLawyerResources(),
        ]);
    }

    /**
     * @param list<CouncilMember> $members
     * @return array<int, string>
     */
    private function resolveCouncilPortraits(array $members): array
    {
        $mediaIds = array_values(array_filter(array_map(static fn (CouncilMember $member): ?int => $member->portraitMediaId, $members), static fn (?int $mediaId): bool => $mediaId !== null));
        if ($mediaIds === []) {
            return [];
        }

        return $this->mediaUrls->resolveMany($mediaIds);
    }

    /**
     * @param list<PagePersonGroup> $groups
     * @return array<int, string>
     */
    private function resolvePagePersonPortraits(array $groups): array
    {
        $mediaIds = [];
        foreach ($groups as $group) {
            foreach ($group->entries as $entry) {
                if ($entry->portraitMediaId !== null) {
                    $mediaIds[] = $entry->portraitMediaId;
                }
            }
        }

        return $mediaIds === [] ? [] : $this->mediaUrls->resolveMany(array_values(array_unique($mediaIds)));
    }

    /** @return list<PublishedPage> */
    private function findContextualPages(PublishedPage $page): array
    {
        if ($page->group === null) {
            return [];
        }

        $pages = $this->handleQuery(new FindPublishedPagesByGroupQuery($page->group));
        return count($pages) > 1 ? $pages : [];
    }
}
