<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Presenter\Controller\AuthLog;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\IdentityContext\Domain\Exception\User\UserNotFoundException;
use Websymphonie\LogContext\Application\Usecase\Query\AuthLog\GetAuthLogDetailsQuery;
use Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Entity\AuthLog\AuthLog;
use Websymphonie\SharedContext\Domain\Service\Helper\BreadcrumsServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/auth-log/{id}/view', name: 'app_auth_log_view', requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
#[HasGroupAccess(RoleGroupEnum::LOGS)]
final class GetAuthLogDetailController extends AbstractController
{

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[IsGranted(new Expression('is_granted("ROLE_VIEW")'))]
    public function __invoke(int $id, BreadcrumsServiceInterface $breadcrumsService): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        try {
            /** @var AuthLog $authLog */
            $authLog = $this->handleQuery(new GetAuthLogDetailsQuery($id));
        } catch (UserNotFoundException) {
            throw $this->createNotFoundException();
        }
        $title = $authLog->getUserIP();
        $breadcrumsService->addBreadcrumb("Connexion", $this->generateUrl('app_auth_log_index'));
        $breadcrumsService->addBreadcrumb($title, $this->generateUrl('app_auth_log_view', ['id' => $id]));
        return $this->render('logs/authlog/view.html.twig', [
            'title' => $title,
            'authLog' => $authLog,
            'breadcrumbs' => $breadcrumsService->getBreadcrumbs(),
        ]);
    }
}