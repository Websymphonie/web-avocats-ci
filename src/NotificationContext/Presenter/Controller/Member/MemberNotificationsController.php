<?php

declare(strict_types=1);

namespace Websymphonie\NotificationContext\Presenter\Controller\Member;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\NotificationContext\Application\Usecase\Command\Notification\DeleteNotificationCommand;
use Websymphonie\NotificationContext\Application\Usecase\Command\Notification\DeleteNotificationsCommand;
use Websymphonie\NotificationContext\Application\Usecase\Command\Notification\ReadAllNotificationsCommand;
use Websymphonie\NotificationContext\Application\Usecase\Command\Notification\ReadNotificationCommand;
use Websymphonie\NotificationContext\Application\Usecase\Query\Notification\GetNotificationDetailsQuery;
use Websymphonie\NotificationContext\Application\Usecase\Query\Notification\GetNotificationListQuery;
use Websymphonie\NotificationContext\Domain\Exception\Notification\NotificationNotFound;
use Websymphonie\NotificationContext\Presenter\Form\Notification\NotificationFilterType;
use Websymphonie\NotificationContext\Presenter\ViewModel\Notification\NotificationDetailViewModel;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;
use Websymphonie\SharedContext\Presenter\Service\SafeRedirectUrlResolver;
use Websymphonie\SharedContext\Presenter\ViewModel\PaginateListViewModel;

#[HasGroupAccess(RoleGroupEnum::ALL)]
final class MemberNotificationsController extends AbstractController
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route(path: '/espace/notifications', name: 'member_notifications_index', methods: ['GET'])]
    #[IsGranted(new Expression('is_granted("ROLE_LIST")'))]
    public function index(
        Request $request,
        ContextServiceInterface $contextService,
        #[MapQueryString] GetNotificationListQuery $query,
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $query->limit = $contextService->getPaginatorPageSize();
        $query->page = $request->query->getInt('page', 1);
        $title = 'Notifications';

        $form = $this->createForm(NotificationFilterType::class, $query, ['user' => $this->getUser()]);
        $form->handleRequest($request);

        /** @var PaginateListViewModel $notifications */
        $notifications = $this->handleQuery($query);

        return $this->render('notifications/index.html.twig', [
            'title' => $title,
            'layout' => 'layouts/member.html.twig',
            'query' => $query,
            'form' => $form->createView(),
            'notifications' => $notifications,
            'list_route' => 'member_notifications_index',
            'view_route' => 'member_notifications_view',
            'read_all_route' => 'member_notifications_read_all',
            'bulk_delete_route' => 'member_notifications_bulk_delete',
        ]);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route(path: '/espace/notifications/{id}', name: 'member_notifications_view', requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
    #[IsGranted(new Expression('is_granted("ROLE_VIEW")'))]
    public function view(int $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        try {
            /** @var NotificationDetailViewModel $notification */
            $notification = $this->handleQuery(new GetNotificationDetailsQuery($id));
            $this->handleCommand(new ReadNotificationCommand($notification->object->id));
        } catch (NotificationNotFound) {
            throw $this->createNotFoundException();
        }

        return $this->render('notifications/view.html.twig', [
            'title' => 'Détails de la notification',
            'layout' => 'layouts/member.html.twig',
            'notification' => $notification->getNotification(),
            'list_route' => 'member_notifications_index',
        ]);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route(path: '/espace/notifications/read-all', name: 'member_notifications_read_all', methods: ['POST'])]
    #[IsGranted(new Expression('is_granted("ROLE_EDIT")'))]
    public function readAll(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $redirectUrl = SafeRedirectUrlResolver::resolve($request, $this->notificationListUrl());

        if (!$this->isCsrfTokenValid('notification_read_all', $request->request->getString('_token'))) {
            $this->flash()->danger('Votre session a expiré. Rechargez la page puis réessayez.');

            return new RedirectResponse($redirectUrl);
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            return new RedirectResponse($redirectUrl);
        }

        try {
            $this->handleCommand(new ReadAllNotificationsCommand(userId: $user->getId()));
            $this->flash()->success('Toutes les notifications ont été marquées comme lues.');
        } catch (UserFacingError $exception) {
            $this->flash()->errorFromException($exception);
        }

        return new RedirectResponse($redirectUrl);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route(path: '/espace/notifications/bulk-delete', name: 'member_notifications_bulk_delete', methods: ['POST'])]
    #[IsGranted(new Expression('is_granted("ROLE_DELETE")'))]
    public function bulkDelete(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $redirectUrl = SafeRedirectUrlResolver::resolve($request, $this->notificationListUrl());

        if (!$this->isCsrfTokenValid('bulk_delete_notifications', $request->request->getString('_token'))) {
            $this->flash()->danger('Votre session a expiré. Rechargez la page puis réessayez.');

            return new RedirectResponse($redirectUrl);
        }

        $ids = $this->parseIds($request->request->all('ids'));
        if ($ids === []) {
            $this->flash()->warning('Sélectionnez au moins une notification.');

            return new RedirectResponse($redirectUrl);
        }

        try {
            $deletedCount = (int) $this->handleCommand(new DeleteNotificationsCommand($ids));
            $missingCount = count($ids) - $deletedCount;

            if ($missingCount > 0) {
                $this->flash()->warning(sprintf(
                    '%d notification%s supprimée%s, %d introuvable%s ou inaccessible%s.',
                    $deletedCount,
                    $deletedCount > 1 ? 's' : '',
                    $deletedCount > 1 ? 's' : '',
                    $missingCount,
                    $missingCount > 1 ? 's' : '',
                    $missingCount > 1 ? 's' : '',
                ));
            } else {
                $this->flash()->success(sprintf(
                    '%d notification%s supprimée%s.',
                    $deletedCount,
                    $deletedCount > 1 ? 's' : '',
                    $deletedCount > 1 ? 's' : '',
                ));
            }
        } catch (UserFacingError $exception) {
            $this->flash()->errorFromException($exception);
        }

        return new RedirectResponse($redirectUrl);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route(path: '/espace/notifications/{id}/delete', name: 'member_notifications_delete', requirements: ['id' => Requirement::DIGITS], methods: ['DELETE'])]
    #[IsGranted(new Expression('is_granted("ROLE_DELETE")'))]
    public function delete(Request $request, int $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $redirectUrl = SafeRedirectUrlResolver::resolve($request, $this->notificationListUrl());

        if (!$this->isCsrfTokenValid('delete' . $id, $request->getPayload()->getString('_token'))) {
            $this->flash()->danger('Votre session a expiré. Rechargez la page puis réessayez.');

            return new RedirectResponse($redirectUrl);
        }

        try {
            $this->handleCommand(new DeleteNotificationCommand($id));
            $this->flash()->success('Notification supprimée avec succès.');
        } catch (UserFacingError $exception) {
            $this->flash()->errorFromException($exception);
        }

        return new RedirectResponse($redirectUrl);
    }

    private function notificationListUrl(): string
    {
        return $this->generateUrl('member_notifications_index');
    }

    /** @return list<int> */
    private function parseIds(mixed $rawIds): array
    {
        if (!is_array($rawIds)) {
            return [];
        }

        $ids = [];
        foreach ($rawIds as $rawId) {
            if (is_int($rawId) && $rawId > 0) {
                $ids[] = $rawId;
                continue;
            }

            if (is_string($rawId) && ctype_digit($rawId) && (int) $rawId > 0) {
                $ids[] = (int) $rawId;
            }
        }

        return array_values(array_unique($ids));
    }
}
