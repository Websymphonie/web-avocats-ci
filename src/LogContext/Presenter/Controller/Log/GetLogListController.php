<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Presenter\Controller\Log;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\LogContext\Application\Usecase\Query\Log\GetLogListQuery;
use Websymphonie\LogContext\Presenter\Form\Log\LogFilterType;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;
use Websymphonie\SharedContext\Domain\Service\Helper\BreadcrumsServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;
use Websymphonie\SharedContext\Presenter\ViewModel\PaginateListViewModel;

#[Route('/list', name: 'app_log_index', methods: ['GET'])]
#[HasGroupAccess(RoleGroupEnum::LOGS)]
final class GetLogListController extends AbstractController
{

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[IsGranted(new Expression('is_granted("ROLE_LIST")'))]
    public function __invoke(
        Request                    $request,
        BreadcrumsServiceInterface $breadcrumsService,
        ContextServiceInterface    $contextService,
        #[MapQueryString]
        GetLogListQuery            $query
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $query->limit = $contextService->getPaginatorPageSize();
        $title = "Liste des logs";
        $breadcrumsService->addBreadcrumb($title, $this->generateUrl('app_log_index'));

        $form = $this->createForm(LogFilterType::class, $query, [
            'method' => 'GET',
        ]);
        $form->handleRequest($request);

        /** @var PaginateListViewModel $logs */
        $logs = $this->handleQuery($query);
        return $this->render('logs/log/index.html.twig', [
            'title' => $title,
            'query' => $query,
            'form' => $form->createView(),
            'logs' => $logs,
            'breadcrumbs' => $breadcrumsService->getBreadcrumbs(),
        ]);
    }
}
