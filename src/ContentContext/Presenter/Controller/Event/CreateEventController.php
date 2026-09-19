<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\Event\CreateEventCommand;
use Websymphonie\ContentContext\Presenter\Form\Event\EventFormType;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/events', name: 'content_admin_event_')]
#[IsGranted('CONTENT_EVENT_MANAGE')]
final class CreateEventController extends AbstractController
{
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response { $command = new CreateEventCommand(); $form = $this->createForm(EventFormType::class, $command); $form->handleRequest($request); if ($form->isSubmitted() && $form->isValid()) { try { $this->handleCommand($command); $this->flash()->success('Événement créé en brouillon.'); return $this->redirectToRoute('content_admin_event_list'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } } return $this->render('content/admin/event/create.html.twig', ['form' => $form->createView(), 'coverUrl' => null, 'photoGallery' => null]); }
}
