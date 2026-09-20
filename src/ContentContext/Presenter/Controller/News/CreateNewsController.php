<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\News;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\News\CreateNewsCommand;
use Websymphonie\ContentContext\Presenter\Form\News\NewsFormType;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/news', name: 'content_admin_news_')]
#[IsGranted('CONTENT_NEWS_MANAGE')]
#[HasGroupAccess(RoleGroupEnum::NEWS)]
final class CreateNewsController extends AbstractController
{
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $command = new CreateNewsCommand();
        $form = $this->createForm(NewsFormType::class, $command);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try { $this->handleCommand($command); $this->flash()->success('Actualité créée en brouillon.'); }
            catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); }
            return $this->redirectToRoute('content_admin_news_list');
        }

        return $this->render('content/admin/news/create.html.twig', ['form' => $form->createView(), 'coverUrl' => null, 'photoGallery' => null]);
    }
}
