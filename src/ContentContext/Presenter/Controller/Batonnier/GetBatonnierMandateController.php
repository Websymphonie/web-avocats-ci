<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\Batonnier;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Query\Batonnier\GetBatonnierMandateQuery;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/batonnier', name: 'content_admin_batonnier_')]
#[IsGranted('BATONNIER_VIEW')]
#[HasGroupAccess(RoleGroupEnum::BATONNIER)]
final class GetBatonnierMandateController extends AbstractController
{
    public function __construct(private readonly MediaPublicUrlResolverInterface $mediaUrls) {}

    #[Route('/{id}', name: 'show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function __invoke(int $id): Response
    {
        $mandate = $this->handleQuery(new GetBatonnierMandateQuery($id));
        $portraitUrls = $mandate->portraitMediaId !== null ? $this->mediaUrls->resolveMany([$mandate->portraitMediaId]) : [];

        return $this->render('content/admin/batonnier/show.html.twig', ['mandate' => $mandate, 'portraitUrl' => $portraitUrls[$mandate->portraitMediaId] ?? null]);
    }
}
