<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Presenter\Controller\Audit;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\LogContext\Application\Usecase\Query\Audit\GetAuditEntryListQuery;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;
use Websymphonie\SharedContext\Domain\Service\Helper\BreadcrumsServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/audit', name: 'app_audit_index', methods: ['GET'])]
#[HasGroupAccess(RoleGroupEnum::LOGS)]
final class GetAuditEntryListController extends AbstractController
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[IsGranted(new Expression('is_granted("ROLE_LIST")'))]
    public function __invoke(
        Request $request,
        BreadcrumsServiceInterface $breadcrumsService,
        ContextServiceInterface $contextService,
        #[MapQueryString] GetAuditEntryListQuery $query,
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $query->limit = $contextService->getPaginatorPageSize();
        $title = 'Audit métier';
        $breadcrumsService->addBreadcrumb($title, $this->generateUrl('app_audit_index'));

        $page = $this->handleQuery($query);

        return $this->render('logs/audit/index.html.twig', [
            'title' => $title,
            'query' => $query,
            'auditPage' => $page,
            'breadcrumbs' => $breadcrumsService->getBreadcrumbs(),
        ]);
    }
}
