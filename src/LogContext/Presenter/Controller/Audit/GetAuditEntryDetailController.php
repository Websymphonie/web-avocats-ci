<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Presenter\Controller\Audit;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\LogContext\Application\Usecase\Query\Audit\GetAuditEntryQuery;
use Websymphonie\SharedContext\Domain\Service\Helper\BreadcrumsServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/audit/{id}/view', name: 'app_audit_view', requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
#[HasGroupAccess(RoleGroupEnum::LOGS)]
final class GetAuditEntryDetailController extends AbstractController
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
            $auditEntry = $this->handleQuery(new GetAuditEntryQuery($id));
        } catch (Exception) {
            throw $this->createNotFoundException();
        }

        $title = 'Détail audit métier';
        $breadcrumsService->addBreadcrumb('Audit métier', $this->generateUrl('app_audit_index'));
        $breadcrumsService->addBreadcrumb($title, $this->generateUrl('app_audit_view', ['id' => $id]));

        return $this->render('logs/audit/view.html.twig', [
            'title' => $title,
            'auditEntry' => $auditEntry,
            'breadcrumbs' => $breadcrumsService->getBreadcrumbs(),
        ]);
    }
}
