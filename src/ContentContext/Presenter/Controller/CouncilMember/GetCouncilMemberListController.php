<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\CouncilMember;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Query\CouncilMember\GetCouncilMemberListQuery;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/conseil-ordre', name: 'content_admin_council_member_')]
#[IsGranted('COUNCIL_MEMBER_LIST')]
#[HasGroupAccess(RoleGroupEnum::COUNCIL_MEMBERS)]
final class GetCouncilMemberListController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->render('content/admin/council_member/index.html.twig', [
            'members' => $this->handleQuery(new GetCouncilMemberListQuery()),
        ]);
    }
}
