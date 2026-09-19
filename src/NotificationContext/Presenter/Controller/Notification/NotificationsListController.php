<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Presenter\Controller\Notification;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\NotificationContext\Application\Usecase\Query\Notification\GetNotificationListQuery;
use Websymphonie\NotificationContext\Presenter\Form\Notification\NotificationFilterType;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;
use Websymphonie\SharedContext\Domain\Service\Helper\BreadcrumsServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;
use Websymphonie\SharedContext\Presenter\ViewModel\PaginateListViewModel;

#[Route(path: '/list', name: 'app_notifications_index', methods: ['GET'])]
#[HasGroupAccess(RoleGroupEnum::ALL)]
class NotificationsListController extends AbstractController
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
        GetNotificationListQuery   $query
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $query->limit = $contextService->getPaginatorPageSize();
        $query->page = $request->query->getInt('page', 1);
        $title = "Notifications";
        $breadcrumsService->addBreadcrumb($title, $this->generateUrl('app_notifications_index'));

        $form = $this->createForm(NotificationFilterType::class, $query, ['user' => $this->getUser()]);
        $form->handleRequest($request);

        /** @var PaginateListViewModel $notifications */
        $notifications = $this->handleQuery($query);

        return $this->render('notifications/index.html.twig', [
            'title' => $title,
            'query' => $query,
            'form' => $form->createView(),
            'notifications' => $notifications,
            'breadcrumbs' => $breadcrumsService->getBreadcrumbs(),
        ]);
    }
}
