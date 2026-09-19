<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\EventCategory;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\EventCategory\DeleteEventCategoryCommand;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/event-categories', name: 'content_admin_event_category_')]
#[IsGranted('CONTENT_EVENT_CATEGORY_DELETE')]
final class DeleteEventCategoryController extends AbstractController
{
    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\\d+'], methods: ['DELETE'])]
    public function __invoke(Request $request, int $id): Response { if (!$this->isCsrfTokenValid('delete' . $id, (string) $request->request->get('_token'))) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); } try { $this->handleCommand(new DeleteEventCategoryCommand($id)); $this->flash()->success('Catégorie d’événement supprimée.'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } return $this->redirectToRoute('content_admin_event_category_list'); }
}
