<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\Event\UpdateEventCommand;
use Websymphonie\ContentContext\Application\Usecase\Query\Event\GetEventDetailsQuery;
use Websymphonie\ContentContext\Presenter\Form\Event\EventFormType;
use Websymphonie\ContentContext\Domain\Repository\PhotoGalleryRepositoryInterface;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/events', name: 'content_admin_event_')]
#[IsGranted('CONTENT_EVENT_MANAGE')]
final class UpdateEventController extends AbstractController
{
    public function __construct(private readonly MediaPublicUrlResolverInterface $mediaUrls, private readonly PhotoGalleryRepositoryInterface $galleryRepository) {}
    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function __invoke(Request $request, int $id): Response { $event = $this->handleQuery(new GetEventDetailsQuery($id)); $command = new UpdateEventCommand($event->id, $event->title, $event->excerpt, $event->description, $event->format, $event->startsAt, $event->endsAt, $event->venueName, $event->address, $event->onlineUrl, array_map(static fn ($category): int => $category->id, $event->categories), array_map(static fn ($tag): int => $tag->id, $event->tags), photoGalleryId: $event->photoGalleryId); $form = $this->createForm(EventFormType::class, $command); $form->handleRequest($request); if ($form->isSubmitted() && $form->isValid()) { try { $this->handleCommand($command); $this->flash()->success('Événement modifié.'); return $this->redirectToRoute('content_admin_event_list'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } } $photoGallery = $event->photoGalleryId !== null ? $this->galleryRepository->getById($event->photoGalleryId) : null; $coverUrls = $event->coverMediaId !== null ? $this->mediaUrls->resolveMany([$event->coverMediaId]) : []; return $this->render('content/admin/event/edit.html.twig', ['event' => $event, 'form' => $form->createView(), 'coverUrl' => $coverUrls[$event->coverMediaId] ?? null, 'photoGallery' => $photoGallery]); }
}
