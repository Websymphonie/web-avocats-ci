<?php

declare(strict_types=1);

namespace Websymphonie\WebContext\Presenter\Controller\Lawyer;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Websymphonie\LawyerContext\Application\Usecase\Query\GetPublicCabinetProfileQuery;
use Websymphonie\LawyerContext\Domain\Model\CabinetPublicProfile;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/cabinets', name: 'web_cabinet_directory_')]
final class GetPublicCabinetProfileController extends AbstractController
{
    public function __construct(private readonly MediaPublicUrlResolverInterface $mediaUrls)
    {
    }

    #[Route('/{uuid}', name: 'profile', requirements: ['uuid' => '[0-9a-fA-F-]{36}'], methods: ['GET'])]
    public function __invoke(string $uuid): Response
    {
        /** @var CabinetPublicProfile|null $cabinet */
        $cabinet = $this->handleQuery(new GetPublicCabinetProfileQuery($uuid));
        if ($cabinet === null) {
            throw $this->createNotFoundException();
        }

        $portraitIds = array_values(array_unique(array_filter(array_map(
            static fn ($member): ?int => $member->portraitMediaId,
            $cabinet->members,
        ))));

        return $this->render('web/cabinets/show.html.twig', [
            'cabinet' => $cabinet,
            'memberMediaUrls' => $this->mediaUrls->resolveMany($portraitIds),
            'phoneHref' => $this->safePhoneHref($cabinet->phone),
            'websiteHref' => $this->safeWebsiteHref($cabinet->websiteUrl),
        ]);
    }

    private function safePhoneHref(?string $phone): ?string
    {
        if ($phone === null || !preg_match('/^\+?[0-9\s().-]+$/', $phone)) {
            return null;
        }

        $normalized = preg_replace('/[\s().-]/', '', $phone);

        return is_string($normalized) && preg_match('/^\+?[0-9]+$/', $normalized) ? 'tel:' . $normalized : null;
    }

    private function safeWebsiteHref(?string $website): ?string
    {
        if ($website === null) {
            return null;
        }

        $scheme = parse_url($website, PHP_URL_SCHEME);
        $host = parse_url($website, PHP_URL_HOST);

        return in_array(strtolower((string) $scheme), ['http', 'https'], true)
            && is_string($host)
            && filter_var($website, FILTER_VALIDATE_URL) !== false
                ? $website
                : null;
    }
}
