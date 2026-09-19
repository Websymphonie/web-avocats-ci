<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Presenter\Controller\Log;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\LogContext\Application\Usecase\Query\Log\GetLogDetailsQuery;
use Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Entity\Log\Logs;
use Websymphonie\SharedContext\Domain\Service\Helper\BreadcrumsServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/{id}/view', name: 'app_log_view', requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
#[HasGroupAccess(RoleGroupEnum::LOGS)]
final class GetLogDetailController extends AbstractController
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
            /** @var Logs $log */
            $log = $this->handleQuery(new GetLogDetailsQuery($id));
        } catch (Exception) {
            throw $this->createNotFoundException();
        }
        $title = "Détails log";
        $breadcrumsService->addBreadcrumb("Logs applicatifs", $this->generateUrl('app_log_index'));
        $breadcrumsService->addBreadcrumb($title, $this->generateUrl('app_log_view', ['id' => $id]));
        return $this->render('logs/log/view.html.twig', [
            'title' => $title,
            'log' => $log,
            'breadcrumbs' => $breadcrumsService->getBreadcrumbs(),
        ]);
    }
}
