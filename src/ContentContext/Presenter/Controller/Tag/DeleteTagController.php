<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Controller\Tag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\Tag\DeleteTagCommand;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;
#[Route('/tags', name: 'content_admin_tag_')]
#[IsGranted('CONTENT_TAG_DELETE')]
final class DeleteTagController extends AbstractController
{
    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\\d+'], methods: ['DELETE'])]
    public function __invoke(Request $request, int $id): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $id, (string) $request->request->get('_token'))) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); }
        try { $this->handleCommand(new DeleteTagCommand($id)); $this->flash()->success('Tag supprimé.'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); }
        return $this->redirectToRoute('content_admin_tag_list');
    }
}
