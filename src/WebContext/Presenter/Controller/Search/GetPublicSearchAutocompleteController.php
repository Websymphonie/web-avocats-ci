<?php

declare(strict_types=1);

namespace Websymphonie\WebContext\Presenter\Controller\Search;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Websymphonie\SharedContext\Presenter\AbstractController;
use Websymphonie\WebContext\Application\Model\PublicSearchSuggestion;
use Websymphonie\WebContext\Application\Usecase\Query\GetPublicSearchSuggestionsQuery;

#[Route('/recherche', name: 'web_search_')]
final class GetPublicSearchAutocompleteController extends AbstractController
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/autocomplete', name: 'autocomplete', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $term = trim((string) $request->query->get('q', ''));
        if ($term === '' || mb_strlen($term) < 2) {
            return new JsonResponse(['query' => $term, 'results' => []]);
        }

        /** @var list<PublicSearchSuggestion> $suggestions */
        $suggestions = $this->handleQuery(new GetPublicSearchSuggestionsQuery($term));
        $results = [];
        foreach ($suggestions as $suggestion) {
            $results[] = [
                'type' => $suggestion->type,
                'title' => $suggestion->title,
                'url' => $this->urlFor($suggestion),
                'metadata' => $suggestion->metadata,
            ];
        }

        return new JsonResponse(['query' => $term, 'results' => $results]);
    }

    private function urlFor(PublicSearchSuggestion $suggestion): string
    {
        return match ($suggestion->type) {
            'news' => $this->generateUrl('web_news_detail', ['slug' => $suggestion->slug]),
            'event' => $this->generateUrl('web_events_detail', ['slug' => $suggestion->slug]),
            'training' => $this->generateUrl('web_trainings_detail', ['slug' => $suggestion->slug]),
            'information' => $suggestion->slug === 'lbc-ft-fp'
                ? $this->generateUrl('web_lbc_ft_fp')
                : $this->generateUrl($suggestion->isCarpaPage ? 'web_carpa_page_detail' : ($suggestion->isBarPage ? 'web_bar_page_detail' : 'web_information_detail'), ['slug' => $suggestion->slug]),
            default => throw new \LogicException('Type de résultat public inconnu.'),
        };
    }
}
