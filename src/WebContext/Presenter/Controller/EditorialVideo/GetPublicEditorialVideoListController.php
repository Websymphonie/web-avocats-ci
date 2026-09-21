<?php

declare(strict_types=1);

namespace Websymphonie\WebContext\Presenter\Controller\EditorialVideo;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Websymphonie\ContentContext\Application\Usecase\Query\EditorialVideo\GetPublishedEditorialVideoListQuery;
use Websymphonie\ContentContext\Domain\Enum\VideoProvider;
use Websymphonie\ContentContext\Domain\Model\EditorialVideoListResult;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/videos', name: 'web_videos_')]
final class GetPublicEditorialVideoListController extends AbstractController
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function __invoke(Request $request, ContextServiceInterface $context): Response
    {
        /** @var EditorialVideoListResult $videos */
        $videos = $this->handleQuery(new GetPublishedEditorialVideoListQuery(
            page: max(1, $request->query->getInt('page', 1)),
            limit: max(1, $context->getPaginatorPageSize()),
        ));

        $thumbnailUrls = [];
        foreach ($videos->items as $video) {
            if ($video->provider === VideoProvider::YOUTUBE && $video->externalVideoId !== null) {
                $thumbnailUrls[$video->id] = 'https://i.ytimg.com/vi/' . rawurlencode($video->externalVideoId) . '/hqdefault.jpg';
            }
        }

        return $this->render('web/videos/index.html.twig', [
            'videos' => $videos,
            'thumbnailUrls' => $thumbnailUrls,
        ]);
    }
}
