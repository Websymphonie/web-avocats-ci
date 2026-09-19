<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\Event\PublishEventCommand;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/events', name: 'content_admin_event_')]
#[IsGranted('CONTENT_EVENT_PUBLISH')]
final class PublishEventController extends AbstractController
{
    #[Route('/{id}/publish', name: 'publish', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function __invoke(Request $request, int $id): Response { $this->assertCsrf($request, 'event_publish_' . $id); try { $this->handleCommand(new PublishEventCommand($id)); $this->flash()->success('Événement publié.'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } return $this->redirectToRoute('content_admin_event_list'); }
    private function assertCsrf(Request $request, string $id): void { if (!$this->isCsrfTokenValid($id, (string) $request->request->get('_token'))) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); } }
}
