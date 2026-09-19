<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\EventCategory;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\EventCategory\UpdateEventCategoryCommand;
use Websymphonie\ContentContext\Application\Usecase\Query\EventCategory\GetEventCategoryDetailsQuery;
use Websymphonie\ContentContext\Presenter\Form\EventCategory\EventCategoryFormType;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/event-categories', name: 'content_admin_event_category_')]
#[IsGranted('CONTENT_EVENT_CATEGORY_MANAGE')]
final class UpdateEventCategoryController extends AbstractController
{
    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function __invoke(Request $request, int $id): Response { $category = $this->handleQuery(new GetEventCategoryDetailsQuery($id)); $command = new UpdateEventCategoryCommand($category->id, $category->name, $category->description); $form = $this->createForm(EventCategoryFormType::class, $command); $form->handleRequest($request); if ($form->isSubmitted() && $form->isValid()) { try { $this->handleCommand($command); $this->flash()->success('Catégorie d’événement modifiée.'); return $this->redirectToRoute('content_admin_event_category_list'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } } return $this->render('content/admin/event_category/form.html.twig', ['title' => 'Modifier la catégorie d’événement', 'form' => $form->createView()]); }
}
