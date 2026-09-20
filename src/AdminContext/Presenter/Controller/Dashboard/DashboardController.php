<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Presenter\Controller\Dashboard;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Service\Helper\BreadcrumsServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route(path: '/dashboard')]
#[HasGroupAccess(RoleGroupEnum::ADMIN)]
class DashboardController extends AbstractController
{

    #[Route(path: '/', name: 'app_admin')]
    public function index(
        BreadcrumsServiceInterface $breadcrumsService
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $title = "Tableau de bord";
        $breadcrumsService->addBreadcrumb($title, $this->generateUrl('app_admin'));
        return $this->render('admin/dashboard/dashboard.html.twig', [
            'title' => $title,
            'breadcrumbs' => $breadcrumsService->getBreadcrumbs(),
        ]);
    }
}
