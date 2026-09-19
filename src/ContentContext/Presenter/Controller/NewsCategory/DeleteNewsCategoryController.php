<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Controller\NewsCategory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\NewsCategory\DeleteNewsCategoryCommand;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;
#[Route('/news-categories', name: 'content_admin_news_category_')]
#[IsGranted('CONTENT_NEWS_CATEGORY_DELETE')]
final class DeleteNewsCategoryController extends AbstractController
{
    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\\d+'], methods: ['DELETE'])]
    public function __invoke(Request $request, int $id): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $id, (string) $request->request->get('_token'))) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); }
        try { $this->handleCommand(new DeleteNewsCategoryCommand($id)); $this->flash()->success('Catégorie supprimée.'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); }
        return $this->redirectToRoute('content_admin_news_category_list');
    }
}
