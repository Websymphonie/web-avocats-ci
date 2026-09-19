<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Presenter\Controller\AuthLog;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\LogContext\Application\Usecase\Query\AuthLog\GetAuthLogListQuery;
use Websymphonie\LogContext\Presenter\Form\AuthLog\AuthLogFilterType;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;
use Websymphonie\SharedContext\Domain\Service\Helper\BreadcrumsServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;
use Websymphonie\SharedContext\Presenter\ViewModel\PaginateListViewModel;

#[Route('/auth-log/list', name: 'app_auth_log_index', methods: ['GET'])]
#[HasGroupAccess(RoleGroupEnum::LOGS)]
final class GetAuthLogListController extends AbstractController
{

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[IsGranted(new Expression('is_granted("ROLE_LIST")'))]
    public function __invoke(
        Request                    $request,
        #[MapQueryString]
        GetAuthLogListQuery        $query,
        BreadcrumsServiceInterface $breadcrumsService,
        ContextServiceInterface    $contextService,
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $query->limit = $contextService->getPaginatorPageSize();
        $title = "Gestion des authentifications";
        $breadcrumsService->addBreadcrumb($title, $this->generateUrl('app_auth_log_index'));

        $form = $this->createForm(AuthLogFilterType::class, $query, [
            'method' => 'GET',
        ]);
        $form->handleRequest($request);

        /** @var PaginateListViewModel $authLogs */
        $authLogs = $this->handleQuery($query);

        return $this->render('logs/authlog/index.html.twig', [
            'title' => $title,
            'query' => $query,
            'form' => $form->createView(),
            'authLogs' => $authLogs,
            'breadcrumbs' => $breadcrumsService->getBreadcrumbs(),
        ]);
    }
}
