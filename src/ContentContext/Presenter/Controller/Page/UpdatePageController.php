<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\Page;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\Page\UpdatePageCommand;
use Websymphonie\ContentContext\Application\Usecase\Query\Page\GetPageQuery;
use Websymphonie\ContentContext\Presenter\Form\Page\PageFormType;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/pages', name: 'content_admin_page_')]
#[IsGranted('CONTENT_PAGE_MANAGE')]
#[HasGroupAccess(RoleGroupEnum::PAGES)]
final class UpdatePageController extends AbstractController
{
    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function __invoke(Request $request, int $id): Response
    {
        $page = $this->handleQuery(new GetPageQuery($id));
        $command = new UpdatePageCommand($page->id, $page->title, $page->slug, $page->content);
        $form = $this->createForm(PageFormType::class, $command);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->handleCommand($command);
                $this->flash()->success('Page modifiée.');
                return $this->redirectToRoute('content_admin_page_edit', ['id' => $id]);
            } catch (UserFacingError $exception) {
                $this->flash()->errorFromException($exception);
            }
        }
        return $this->render('content/admin/page/edit.html.twig', ['page' => $page, 'form' => $form->createView()]);
    }
}
