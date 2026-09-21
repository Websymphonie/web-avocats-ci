<?php

declare(strict_types=1);

namespace Websymphonie\WebContext\Presenter\Controller\Event;

use DateTimeImmutable;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Websymphonie\ContentContext\Application\Usecase\Query\Event\GetPublishedEventsListQuery;
use Websymphonie\ContentContext\Application\Usecase\Query\EventCategory\GetEventCategoryListQuery;
use Websymphonie\ContentContext\Domain\Model\Event;
use Websymphonie\ContentContext\Domain\Model\EventCategory;
use Websymphonie\ContentContext\Domain\Model\EventCategoryListResult;
use Websymphonie\ContentContext\Domain\Model\EventListResult;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/evenements', name: 'web_events_')]
final class GetPublicEventListController extends AbstractController
{
    public function __construct(private readonly MediaPublicUrlResolverInterface $mediaUrls)
    {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function __invoke(Request $request, ContextServiceInterface $context): Response
    {
        $categorySlug = trim((string) $request->query->get('categorie', ''));
        /** @var EventCategoryListResult $categoryResult */
        $categoryResult = $this->handleQuery(new GetEventCategoryListQuery(limit: 100));
        $selectedCategory = $this->findCategoryBySlug($categoryResult->items, $categorySlug);

        /** @var EventListResult $events */
        $events = $this->handleQuery(new GetPublishedEventsListQuery(
            page: max(1, $request->query->getInt('page', 1)),
            limit: max(1, $context->getPaginatorPageSize()),
            categoryId: $selectedCategory?->id,
        ));

        $now = new DateTimeImmutable();
        $upcomingEvents = [];
        $pastEvents = [];
        foreach ($events->items as $event) {
            if ($this->isCurrentOrUpcoming($event, $now)) {
                $upcomingEvents[] = $event;
            } else {
                $pastEvents[] = $event;
            }
        }

        $mediaIds = array_values(array_filter(array_map(static fn (Event $event): ?int => $event->coverMediaId, $events->items)));

        return $this->render('web/events/index.html.twig', [
            'events' => $events,
            'upcomingEvents' => $upcomingEvents,
            'pastEvents' => $pastEvents,
            'categories' => $categoryResult->items,
            'selectedCategory' => $selectedCategory,
            'mediaUrls' => $this->mediaUrls->resolveMany($mediaIds),
            'now' => $now,
        ]);
    }

    /** @param list<EventCategory> $categories */
    private function findCategoryBySlug(array $categories, string $slug): ?EventCategory
    {
        if ($slug === '') {
            return null;
        }

        foreach ($categories as $category) {
            if ($category->slug === $slug) {
                return $category;
            }
        }

        return null;
    }

    private function isCurrentOrUpcoming(Event $event, DateTimeImmutable $now): bool
    {
        return $event->startsAt >= $now || ($event->endsAt !== null && $event->endsAt >= $now);
    }
}
