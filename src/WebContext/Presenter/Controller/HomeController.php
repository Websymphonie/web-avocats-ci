<?php
declare(strict_types=1);

namespace Websymphonie\WebContext\Presenter\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Websymphonie\ContentContext\Application\Model\PublishedPage;
use Websymphonie\ContentContext\Application\Usecase\Query\Event\GetPublishedEventsListQuery;
use Websymphonie\ContentContext\Application\Usecase\Query\News\GetPublishedNewsListQuery;
use Websymphonie\ContentContext\Application\Usecase\Query\Page\FindPublishedPageBySlugQuery;
use Websymphonie\ContentContext\Domain\Enum\PageGroup;
use Websymphonie\ContentContext\Domain\Model\EventListResult;
use Websymphonie\ContentContext\Domain\Model\NewsListResult;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route(path: '/', name: 'app_home', methods: ['GET'])]
final class HomeController extends AbstractController
{
    public function __construct(private readonly MediaPublicUrlResolverInterface $mediaUrls)
    {
    }

    public function __invoke(): Response
    {
        /** @var NewsListResult $news */
        $news = $this->handleQuery(new GetPublishedNewsListQuery(limit: 5));
        /** @var EventListResult $events */
        $events = $this->handleQuery(new GetPublishedEventsListQuery(limit: 5));
        /** @var PublishedPage|null $carpaPage */
        $carpaPage = $this->handleQuery(new FindPublishedPageBySlugQuery('carpa'));
        $carpaUrl = $carpaPage !== null && $carpaPage->group === PageGroup::BAR
            ? $this->generateUrl('web_bar_page_detail', ['slug' => $carpaPage->slug])
            : $this->generateUrl('web_barreau_index');

        $mediaIds = [];
        foreach ($news->items as $item) {
            if ($item->coverMediaId !== null) {
                $mediaIds[] = $item->coverMediaId;
            }
        }
        foreach ($events->items as $item) {
            if ($item->coverMediaId !== null) {
                $mediaIds[] = $item->coverMediaId;
            }
        }

        return $this->render('web/home/index.html.twig', [
            'title' => 'Accueil',
            'homepageNews' => $news->items,
            'homepageEvents' => $events->items,
            'carpaUrl' => $carpaUrl,
            'mediaUrls' => $this->mediaUrls->resolveMany(array_values(array_unique($mediaIds))),
        ]);
    }
}
