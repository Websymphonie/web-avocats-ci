<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\Event;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Application\Usecase\Query\Event\GetEventDetailsQuery;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/events', name: 'content_admin_event_')]
#[IsGranted('CONTENT_EVENT_VIEW')]
#[HasGroupAccess(RoleGroupEnum::EVENTS)]
final class GetEventDetailsController extends AbstractController
{
    public function __construct(private readonly RichTextSanitizerInterface $sanitizer) {}
    #[Route('/{id}', name: 'show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function __invoke(int $id): Response { $event = $this->handleQuery(new GetEventDetailsQuery($id)); return $this->render('content/admin/event/show.html.twig', ['event' => $event, 'safeDescription' => $this->sanitizer->sanitize($event->description)]); }
}
