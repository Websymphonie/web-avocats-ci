<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Presenter\Controller\Notification;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\NotificationContext\Application\Usecase\Command\Notification\ReadAllNotificationsCommand;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;
use Websymphonie\SharedContext\Presenter\Service\SafeRedirectUrlResolver;

#[Route(path: '/read-all', name: 'app_notifications_read_all', methods: ['POST'])]
#[HasGroupAccess(RoleGroupEnum::ALL)]
class ReadAllNotificationsController extends AbstractController
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    #[IsGranted(new Expression('is_granted("ROLE_EDIT")'))]
    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!$this->isCsrfTokenValid('notification_read_all', $request->request->getString('_token'))) {
            $this->flash()->danger('Votre session a expiré. Rechargez la page puis réessayez.');

            return new RedirectResponse(SafeRedirectUrlResolver::resolve(
                $request,
                $this->generateUrl('app_notifications_index')
            ));
        }

        /** @var User $user */
        $user = $this->getUser();

        try {
            $this->handleCommand(new ReadAllNotificationsCommand(userId: $user->getId()));
            $this->flash()->success('Toutes les notifications ont été marquées comme lues.');
        } catch (UserFacingError $e) {
            $this->flash()->errorFromException($e);
        }
        return new RedirectResponse(SafeRedirectUrlResolver::resolve(
            $request,
            $this->generateUrl('app_notifications_index')
        ));
    }
}
