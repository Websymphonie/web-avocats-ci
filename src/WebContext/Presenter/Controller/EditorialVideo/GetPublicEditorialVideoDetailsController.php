<?php

declare(strict_types=1);

namespace Websymphonie\WebContext\Presenter\Controller\EditorialVideo;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Application\Usecase\Query\EditorialVideo\GetPublishedEditorialVideoBySlugQuery;
use Websymphonie\ContentContext\Domain\Exception\EditorialVideoNotFoundException;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/videos', name: 'web_videos_')]
final class GetPublicEditorialVideoDetailsController extends AbstractController
{
    public function __construct(private readonly RichTextSanitizerInterface $sanitizer)
    {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/{slug}', name: 'detail', requirements: ['slug' => '[a-z0-9]+(?:-[a-z0-9]+)*'], methods: ['GET'])]
    public function __invoke(string $slug): Response
    {
        try {
            $video = $this->handleQuery(new GetPublishedEditorialVideoBySlugQuery($slug));
        } catch (EditorialVideoNotFoundException) {
            throw $this->createNotFoundException();
        }

        return $this->render('web/videos/show.html.twig', [
            'video' => $video,
            'embedUrl' => $video->youtubeEmbedUrl(),
            'safeDescription' => $this->sanitizer->sanitize($video->description),
        ]);
    }
}
