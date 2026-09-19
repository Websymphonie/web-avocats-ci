<?php
declare(strict_types=1);

namespace Websymphonie\WebContext\Presenter\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Websymphonie\IdentityContext\Domain\Service\User\UserRolesInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route(path: '/', name: 'app_home', methods: ['GET'])]
class HomeController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('web/home/index.html.twig', [
            'title' => "Bienvenue",
        ]);
    }
}
