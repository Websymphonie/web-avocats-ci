<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Presenter\Controller\Reset;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Websymphonie\SharedContext\Domain\Service\Helper\BreadcrumsServiceInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/user-email-verify', name: 'app_check_email', methods: ['GET'])]
class EmailVerifyController extends AbstractController
{
    public function __invoke(
        Request                          $request,
        BreadcrumsServiceInterface       $breadcrumsService,
    ): Response
    {
        $title = "Vérification d'email";
        $breadcrumsService->addBreadcrumb($title, $this->generateUrl('app_login'));

        return $this->render('auths/reset_password/check_email.html.twig', [
            'title' => $title,
            'breadcrumbs' => $breadcrumsService->getBreadcrumbs(),
        ]);
    }
}
