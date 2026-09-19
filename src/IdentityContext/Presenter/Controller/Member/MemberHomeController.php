<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Controller\Member;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Websymphonie\SharedContext\Presenter\AbstractController;

final class MemberHomeController extends AbstractController
{
    #[Route(path: '/espace', name: 'app_member', methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->render('member/home/index.html.twig', [
            'title' => 'Espace Avocat',
        ]);
    }
}
