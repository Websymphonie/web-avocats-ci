<?php

declare(strict_types=1);

namespace Websymphonie\WebContext\Application\Usecase\QueryHandler;

use Websymphonie\ContentContext\Domain\Model\Event;
use Websymphonie\ContentContext\Domain\Model\News;
use Websymphonie\ContentContext\Domain\Model\Page;
use Websymphonie\ContentContext\Domain\Repository\EventRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\NewsRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\PageRepositoryInterface;
use Websymphonie\LearningContext\Domain\Model\Training;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;
use Websymphonie\WebContext\Application\Model\PublicSearchSuggestion;
use Websymphonie\WebContext\Application\Usecase\Query\GetPublicSearchSuggestionsQuery;

final readonly class GetPublicSearchSuggestionsHandler implements QueryHandler
{
    public function __construct(
        private NewsRepositoryInterface $newsRepository,
        private EventRepositoryInterface $eventRepository,
        private PageRepositoryInterface $pageRepository,
        private TrainingRepositoryInterface $trainingRepository,
    ) {
    }

    /** @return list<PublicSearchSuggestion> */
    public function __invoke(GetPublicSearchSuggestionsQuery $query): array
    {
        $term = trim($query->term);
        if ($term === '' || mb_strlen($term) < 2) {
            return [];
        }

        $limit = min(4, max(1, $query->limitPerType));
        $suggestions = [];

        foreach ($this->newsRepository->searchPublished($term, $limit) as $news) {
            $suggestions[] = $this->newsSuggestion($news);
        }
        foreach ($this->eventRepository->searchPublished($term, $limit) as $event) {
            $suggestions[] = $this->eventSuggestion($event);
        }
        foreach ($this->pageRepository->searchPublished($term, $limit) as $page) {
            $suggestions[] = $this->pageSuggestion($page);
        }
        foreach ($this->trainingRepository->searchPublic($term, $limit) as $training) {
            $suggestions[] = $this->trainingSuggestion($training);
        }

        return $suggestions;
    }

    private function newsSuggestion(News $news): PublicSearchSuggestion
    {
        return new PublicSearchSuggestion(
            type: 'news',
            title: $news->title,
            slug: $news->slug,
            metadata: 'Actualité' . $this->dateSuffix($news->publishedAt),
        );
    }

    private function eventSuggestion(Event $event): PublicSearchSuggestion
    {
        return new PublicSearchSuggestion(
            type: 'event',
            title: $event->title,
            slug: $event->slug,
            metadata: 'Événement' . $this->dateSuffix($event->startsAt),
        );
    }

    private function trainingSuggestion(Training $training): PublicSearchSuggestion
    {
        return new PublicSearchSuggestion(
            type: 'training',
            title: $training->title,
            slug: $training->slug,
            metadata: 'Formation · ' . $training->type->label(),
        );
    }

    private function pageSuggestion(Page $page): PublicSearchSuggestion
    {
        return new PublicSearchSuggestion(
            type: 'information',
            title: $page->title,
            slug: $page->slug,
            metadata: $page->group?->label() ?? 'Informations',
        );
    }

    private function dateSuffix(?\DateTimeImmutable $date): string
    {
        return $date === null ? '' : ' · ' . $date->format('d/m/Y');
    }
}
