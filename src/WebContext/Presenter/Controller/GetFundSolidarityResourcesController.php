<?php

declare(strict_types=1);

namespace Websymphonie\WebContext\Presenter\Controller;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Service\DocumentDownloadPolicy;
use Websymphonie\ContentContext\Application\Usecase\Query\Document\GetMemberFundResourcesQuery;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/espace/ressources/fonds-de-solidarite', name: 'member_fund_resources_index', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class GetFundSolidarityResourcesController extends AbstractController
{
    public function __construct(private readonly string $fundSolidarityTagSlug) {}

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(
        Request $request,
        ContextServiceInterface $context,
        DocumentDownloadPolicy $downloadPolicy,
    ): Response {
        if (!$downloadPolicy->canAccessLawyerResources()) {
            throw $this->createAccessDeniedException('Cette rubrique est réservée aux avocats disposant d’un compte actif.');
        }

        $resources = $this->handleQuery(new GetMemberFundResourcesQuery(
            $this->fundSolidarityTagSlug,
            max(1, $request->query->getInt('page', 1)),
            $context->getPaginatorPageSize(),
        ));

        return $this->render('member/fund_resources/index.html.twig', [
            'title' => 'Fonds de Solidarité',
            'resources' => $resources,
        ]);
    }
}
