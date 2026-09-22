<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\CouncilMember;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Query\CouncilMember\GetCouncilMemberQuery;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/conseil-ordre', name: 'content_admin_council_member_')]
#[IsGranted('COUNCIL_MEMBER_VIEW')]
#[HasGroupAccess(RoleGroupEnum::COUNCIL_MEMBERS)]
final class GetCouncilMemberController extends AbstractController
{
    public function __construct(private readonly MediaPublicUrlResolverInterface $mediaUrls)
    {
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function __invoke(int $id): Response
    {
        $member = $this->handleQuery(new GetCouncilMemberQuery($id));
        $portraitUrls = $member->portraitMediaId !== null ? $this->mediaUrls->resolveMany([$member->portraitMediaId]) : [];

        return $this->render('content/admin/council_member/show.html.twig', [
            'member' => $member,
            'portraitUrl' => $portraitUrls[$member->portraitMediaId] ?? null,
        ]);
    }
}
