<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\EventCategory;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\EventCategory\CreateEventCategoryCommand;
use Websymphonie\ContentContext\Presenter\Form\EventCategory\EventCategoryFormType;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/event-categories', name: 'content_admin_event_category_')]
#[IsGranted('CONTENT_EVENT_CATEGORY_MANAGE')]
#[HasGroupAccess(RoleGroupEnum::CATEGORY_EVENTS)]
final class CreateEventCategoryController extends AbstractController
{
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response { $command = new CreateEventCategoryCommand(); $form = $this->createForm(EventCategoryFormType::class, $command); $form->handleRequest($request); if ($form->isSubmitted() && $form->isValid()) { try { $this->handleCommand($command); $this->flash()->success('Catégorie d’événement créée.'); return $this->redirectToRoute('content_admin_event_category_list'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } } return $this->render('content/admin/event_category/form.html.twig', ['title' => 'Nouvelle catégorie d’événement', 'form' => $form->createView()]); }
}
