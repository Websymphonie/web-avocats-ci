<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Controller\User;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\IdentityContext\Application\Usecase\Command\User\DeleteUsersCommand;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;
use Websymphonie\SharedContext\Presenter\Service\SafeRedirectUrlResolver;

#[Route('/users/bulk-delete', name: 'app_users_bulk_delete', methods: ['POST'])]
#[HasGroupAccess(RoleGroupEnum::USER_ACCOUNT)]
final class DeleteUsersController extends AbstractController
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[IsGranted(new Expression('is_granted("ROLE_DELETE")'))]
    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $redirectUrl = SafeRedirectUrlResolver::resolve(
            $request,
            $this->generateUrl('app_user_index')
        );

        if (!$this->isCsrfTokenValid('bulk_delete_users', $request->request->getString('_token'))) {
            $this->flash()->danger('Votre session a expiré. Rechargez la page puis réessayez.');

            return new RedirectResponse($redirectUrl);
        }

        $payload = $request->request->all();
        $ids = $this->parseIds($payload['ids'] ?? []);
        if ($ids === []) {
            $this->flash()->warning('Sélectionnez au moins un utilisateur.');

            return new RedirectResponse($redirectUrl);
        }

        try {
            $deletedCount = (int) $this->handleCommand(new DeleteUsersCommand($ids));
            $missingCount = count($ids) - $deletedCount;

            if ($missingCount > 0) {
                $this->flash()->warning(sprintf(
                    '%d utilisateur%s supprimé%s, %d introuvable%s.',
                    $deletedCount,
                    $deletedCount > 1 ? 's' : '',
                    $deletedCount > 1 ? 's' : '',
                    $missingCount,
                    $missingCount > 1 ? 's' : '',
                ));
            } else {
                $this->flash()->success(sprintf(
                    '%d utilisateur%s supprimé%s.',
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
