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
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\NotificationContext\Application\Usecase\Command\Notification\DeleteNotificationCommand;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;
use Websymphonie\SharedContext\Presenter\Service\SafeRedirectUrlResolver;

#[Route(path: '/{id}/delete', name: 'app_notifications_delete', requirements: ['id' => Requirement::DIGITS], methods: ['DELETE'])]
#[HasGroupAccess(RoleGroupEnum::ALL)]
class DeleteNotificationsController extends AbstractController
{

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    #[IsGranted(new Expression('is_granted("ROLE_DELETE")'))]
    public function __invoke(Request $request, int $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $redirectUrl = SafeRedirectUrlResolver::resolve(
            $request,
            $this->generateUrl('app_notifications_index')
        );

        if (!$this->isCsrfTokenValid('delete' . $id, $request->getPayload()->getString('_token'))) {
            $this->flash()->danger('Votre session a expiré. Rechargez la page puis réessayez.');

            return new RedirectResponse($redirectUrl);
        }

        try {
            $this->handleCommand(new DeleteNotificationCommand($id));
            $this->flash()->success('Notification supprimée avec succès.');
        } catch (UserFacingError $e) {
            $this->flash()->errorFromException($e);
        }

        return new RedirectResponse($redirectUrl);
    }
}
