<?php

declare(strict_types=1);

namespace Websymphonie\WebContext\Presenter\Controller\Event;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Application\Usecase\Query\Event\GetPublishedEventBySlugQuery;
use Websymphonie\ContentContext\Application\Usecase\Query\EventCategory\GetEventCategoryListQuery;
use Websymphonie\ContentContext\Domain\Exception\EventNotFoundException;
use Websymphonie\ContentContext\Domain\Model\EventCategoryListResult;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/evenements', name: 'web_events_')]
final class GetPublicEventDetailsController extends AbstractController
{
    public function __construct(
        private readonly RichTextSanitizerInterface $sanitizer,
        private readonly MediaPublicUrlResolverInterface $mediaUrls,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/{slug}', name: 'detail', requirements: ['slug' => '[a-z0-9]+(?:-[a-z0-9]+)*'], methods: ['GET'])]
    public function __invoke(string $slug): Response
    {
        try {
            $event = $this->handleQuery(new GetPublishedEventBySlugQuery($slug));
        } catch (EventNotFoundException) {
            throw $this->createNotFoundException();
        }

        $coverUrls = $event->coverMediaId !== null ? $this->mediaUrls->resolveMany([$event->coverMediaId]) : [];
        /** @var EventCategoryListResult $categoryResult */
        $categoryResult = $this->handleQuery(new GetEventCategoryListQuery(limit: 100));
        $selectedCategory = null;
        $currentCategory = $event->categories[0] ?? null;

        if ($currentCategory !== null) {
            foreach ($categoryResult->items as $category) {
                if ($category->id === $currentCategory->id) {
                    $selectedCategory = $category;
                    break;
                }
            }
        }

        return $this->render('web/events/show.html.twig', [
            'event' => $event,
            'categories' => $categoryResult->items,
            'selectedCategory' => $selectedCategory,
            'coverUrl' => $coverUrls[$event->coverMediaId] ?? null,
            'safeBody' => $this->sanitizer->sanitize($event->description),
        ]);
    }
}
