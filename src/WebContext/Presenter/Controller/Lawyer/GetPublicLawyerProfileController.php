<?php

declare(strict_types=1);

namespace Websymphonie\WebContext\Presenter\Controller\Lawyer;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Websymphonie\LawyerContext\Application\Usecase\Query\GetPublicLawyerProfileQuery;
use Websymphonie\LawyerContext\Domain\Model\LawyerPublicProfile;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/avocats', name: 'web_lawyer_directory_')]
final class GetPublicLawyerProfileController extends AbstractController
{
    public function __construct(private readonly MediaPublicUrlResolverInterface $mediaUrls)
    {
    }

    #[Route('/{uuid}', name: 'profile', requirements: ['uuid' => '[0-9a-fA-F-]{36}'], methods: ['GET'])]
    public function __invoke(string $uuid): Response
    {
        /** @var LawyerPublicProfile|null $lawyer */
        $lawyer = $this->handleQuery(new GetPublicLawyerProfileQuery($uuid));
        if ($lawyer === null) {
            throw $this->createNotFoundException();
        }

        $mediaUrls = $lawyer->portraitMediaId !== null ? $this->mediaUrls->resolveMany([$lawyer->portraitMediaId]) : [];

        return $this->render('web/lawyers/show.html.twig', [
            'lawyer' => $lawyer,
            'portraitUrl' => $lawyer->portraitMediaId !== null ? ($mediaUrls[$lawyer->portraitMediaId] ?? null) : null,
            'professionalPhoneHref' => $this->safePhoneHref($lawyer->professionalPhone),
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
}
