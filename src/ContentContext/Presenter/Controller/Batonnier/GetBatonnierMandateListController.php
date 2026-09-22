<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\Batonnier;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Query\Batonnier\GetBatonnierMandateListQuery;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/batonnier', name: 'content_admin_batonnier_')]
#[IsGranted('BATONNIER_LIST')]
#[HasGroupAccess(RoleGroupEnum::BATONNIER)]
final class GetBatonnierMandateListController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function __invoke(): Response
    {
        $mandates = $this->handleQuery(new GetBatonnierMandateListQuery());

        return $this->render('content/admin/batonnier/index.html.twig', ['mandates' => $mandates]);
    }
}
