<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\DocumentPublication;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\Document\ArchiveDocumentPublicationCommand;
use Websymphonie\ContentContext\Application\Usecase\Command\Document\BulkDeleteDocumentPublicationsCommand;
use Websymphonie\ContentContext\Application\Usecase\Command\Document\DeleteDocumentPublicationCommand;
use Websymphonie\ContentContext\Application\Usecase\Command\Document\PublishDocumentPublicationCommand;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/documents', name: 'content_admin_document_')]
#[HasGroupAccess(RoleGroupEnum::DOCUMENTS)]
final class DocumentPublicationActionController extends AbstractController
{
    #[Route('/{id}/publish', name: 'publish', requirements: ['id' => '\\d+'], methods: ['POST'])]
    #[IsGranted('CONTENT_DOCUMENT_PUBLISH')]
    public function publish(Request $request, int $id): Response { return $this->action($request, 'document_publish_' . $id, new PublishDocumentPublicationCommand($id), 'Publication publiée.'); }
    #[Route('/{id}/archive', name: 'archive', requirements: ['id' => '\\d+'], methods: ['POST'])]
    #[IsGranted('CONTENT_DOCUMENT_PUBLISH')]
    public function archive(Request $request, int $id): Response { return $this->action($request, 'document_archive_' . $id, new ArchiveDocumentPublicationCommand($id), 'Publication archivée.'); }
    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\\d+'], methods: ['DELETE'])]
    #[IsGranted('CONTENT_DOCUMENT_DELETE')]
    public function delete(Request $request, int $id): Response { return $this->action($request, 'delete' . $id, new DeleteDocumentPublicationCommand($id), 'Publication supprimée et fichier privé nettoyé si celui-ci était orphelin.'); }
    #[Route('/bulk-delete', name: 'bulk_delete', methods: ['POST'])]
    #[IsGranted('CONTENT_DOCUMENT_DELETE')]
    public function bulkDelete(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('document-bulk-delete', (string) $request->request->get('_token'))) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); }
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $request->request->all('ids')), static fn (int $id): bool => $id > 0)));
        if ($ids !== []) { try { $this->handleCommand(new BulkDeleteDocumentPublicationsCommand($ids)); $this->flash()->success(sprintf('%d publication(s) supprimée(s).', count($ids))); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } }
        return $this->redirectToRoute('content_admin_document_list');
    }
    private function action(Request $request, string $csrf, object $command, string $success): Response
    {
        if (!$this->isCsrfTokenValid($csrf, (string) $request->request->get('_token'))) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); }
        try { $this->handleCommand($command); $this->flash()->success($success); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); }
        return $this->redirectToRoute('content_admin_document_list');
    }
}
