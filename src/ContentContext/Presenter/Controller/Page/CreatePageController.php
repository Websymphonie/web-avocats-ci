<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\Page;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\Page\CreatePageCommand;
use Websymphonie\ContentContext\Presenter\Form\Page\PageFormType;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/pages', name: 'content_admin_page_')]
#[IsGranted('CONTENT_PAGE_MANAGE')]
#[HasGroupAccess(RoleGroupEnum::PAGES)]
final class CreatePageController extends AbstractController
{
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $command = new CreatePageCommand();
        $form = $this->createForm(PageFormType::class, $command);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $page = $this->handleCommand($command);
                $this->flash()->success('Page créée en brouillon.');
                return $this->redirectToRoute('content_admin_page_edit', ['id' => $page->id]);
            } catch (UserFacingError $exception) {
                $this->flash()->errorFromException($exception);
            }
        }
        return $this->render('content/admin/page/create.html.twig', ['form' => $form->createView(), 'coverUrl' => null]);
    }
}
