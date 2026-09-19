<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Presenter\Controller\Notification;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\NotificationContext\Application\Usecase\Command\Notification\ReadNotificationCommand;
use Websymphonie\NotificationContext\Application\Usecase\Query\Notification\GetNotificationDetailsQuery;
use Websymphonie\NotificationContext\Domain\Exception\Notification\NotificationNotFound;
use Websymphonie\NotificationContext\Presenter\ViewModel\Notification\NotificationDetailViewModel;
use Websymphonie\SharedContext\Domain\Service\Helper\BreadcrumsServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route(path: '/{id}/view', name: 'app_notifications_view', requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
#[HasGroupAccess(RoleGroupEnum::ALL)]
class NotificationsDetailsController extends AbstractController
{

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[IsGranted(new Expression('is_granted("ROLE_VIEW")'))]
    public function __invoke(Request $request, int $id, BreadcrumsServiceInterface $breadcrumsService): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $title = "Détails de la notification";
        $breadcrumsService->addBreadcrumb("Notifications", $this->generateUrl('app_notifications_index'));
        $breadcrumsService->addBreadcrumb($title, $this->generateUrl('app_notifications_view', ['id' => $id]));

        try {
            /** @var NotificationDetailViewModel $notification */
            $notification = $this->handleQuery(new GetNotificationDetailsQuery($id));
            $this->markAsReadAndFetchDetails($notification->object->id);
        } catch (NotificationNotFound) {
            throw $this->createNotFoundException();
        }
        return $this->render('notifications/view.html.twig', [
            'title' => $title,
            'notification' => $notification->getNotification(),
            'breadcrumbs' => $breadcrumsService->getBreadcrumbs(),
        ]);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function markAsReadAndFetchDetails(int $id): void
    {
        $this->handleCommand(new ReadNotificationCommand($id));
    }
}
