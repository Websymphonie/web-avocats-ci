<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\Event\BulkDeleteEventsCommand;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/events', name: 'content_admin_event_')]
#[IsGranted('CONTENT_EVENT_DELETE')]
final class BulkDeleteEventsController extends AbstractController
{
    #[Route('/bulk-delete', name: 'bulk_delete', methods: ['POST'])]
    public function __invoke(Request $request): Response { if (!$this->isCsrfTokenValid('event-bulk-delete', (string) $request->request->get('_token'))) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); } $ids = array_values(array_unique(array_filter(array_map('intval', (array) $request->request->all('ids')), static fn (int $id): bool => $id > 0))); if ($ids !== []) { try { $this->handleCommand(new BulkDeleteEventsCommand($ids)); $this->flash()->success(sprintf('%d événement(s) supprimé(s).', count($ids))); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } } return $this->redirectToRoute('content_admin_event_list'); }
}
