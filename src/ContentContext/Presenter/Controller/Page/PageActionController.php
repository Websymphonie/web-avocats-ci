<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\Page;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\Page\BulkDeletePagesCommand;
use Websymphonie\ContentContext\Application\Usecase\Command\Page\DeletePageCommand;
use Websymphonie\ContentContext\Application\Usecase\Command\Page\PublishPageCommand;
use Websymphonie\ContentContext\Application\Usecase\Command\Page\UnpublishPageCommand;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/pages', name: 'content_admin_page_')]
#[HasGroupAccess(RoleGroupEnum::PAGES)]
final class PageActionController extends AbstractController
{
    #[Route('/{id}/publish', name: 'publish', requirements: ['id' => '\\d+'], methods: ['POST'])]
    #[IsGranted('CONTENT_PAGE_PUBLISH')]
    public function publish(Request $request, int $id): Response { return $this->action($request, 'page_publish_' . $id, new PublishPageCommand($id), 'Page publiée.'); }

    #[Route('/{id}/unpublish', name: 'unpublish', requirements: ['id' => '\\d+'], methods: ['POST'])]
    #[IsGranted('CONTENT_PAGE_PUBLISH')]
    public function unpublish(Request $request, int $id): Response { return $this->action($request, 'page_unpublish_' . $id, new UnpublishPageCommand($id), 'Page dépubliée.'); }

    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\\d+'], methods: ['DELETE'])]
    #[IsGranted('CONTENT_PAGE_DELETE')]
    public function delete(Request $request, int $id): Response { return $this->action($request, 'delete' . $id, new DeletePageCommand($id), 'Page supprimée.'); }

    #[Route('/bulk-delete', name: 'bulk_delete', methods: ['POST'])]
    #[IsGranted('CONTENT_PAGE_DELETE')]
    public function bulkDelete(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('page-bulk-delete', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $request->request->all('ids')), static fn (int $id): bool => $id > 0)));
        if ($ids !== []) {
            try {
                $this->handleCommand(new BulkDeletePagesCommand($ids));
                $this->flash()->success(sprintf('%d page(s) supprimée(s).', count($ids)));
            } catch (UserFacingError $exception) {
                $this->flash()->errorFromException($exception);
            }
        }
        return $this->redirectToRoute('content_admin_page_list');
    }

    private function action(Request $request, string $csrf, object $command, string $success): Response
    {
        if (!$this->isCsrfTokenValid($csrf, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }
        try {
            $this->handleCommand($command);
            $this->flash()->success($success);
        } catch (UserFacingError $exception) {
            $this->flash()->errorFromException($exception);
        }
        return $this->redirectToRoute('content_admin_page_list');
    }
}
