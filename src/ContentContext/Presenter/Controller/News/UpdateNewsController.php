<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\News;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\News\UpdateNewsCommand;
use Websymphonie\ContentContext\Application\Usecase\Query\News\GetNewsDetailsQuery;
use Websymphonie\ContentContext\Presenter\Form\News\NewsFormType;
use Websymphonie\ContentContext\Domain\Repository\PhotoGalleryRepositoryInterface;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/news', name: 'content_admin_news_')]
#[IsGranted('CONTENT_NEWS_MANAGE')]
final class UpdateNewsController extends AbstractController
{
    public function __construct(private readonly MediaPublicUrlResolverInterface $mediaUrls, private readonly PhotoGalleryRepositoryInterface $galleryRepository) {}
    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function __invoke(Request $request, int $id): Response
    {
        $news = $this->handleQuery(new GetNewsDetailsQuery($id));
        $command = new UpdateNewsCommand($news->id, $news->title, $news->excerpt, $news->body, array_map(static fn ($category): int => $category->id, $news->categories), array_map(static fn ($tag): int => $tag->id, $news->tags), photoGalleryId: $news->photoGalleryId);
        $form = $this->createForm(NewsFormType::class, $command);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try { $this->handleCommand($command); $this->flash()->success('Actualité modifiée.'); }
            catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); }
            return $this->redirectToRoute('content_admin_news_list');
        }

        $photoGallery = $news->photoGalleryId !== null ? $this->galleryRepository->getById($news->photoGalleryId) : null;
        $coverUrls = $news->coverMediaId !== null ? $this->mediaUrls->resolveMany([$news->coverMediaId]) : [];
        return $this->render('content/admin/news/edit.html.twig', ['news' => $news, 'form' => $form->createView(), 'coverUrl' => $coverUrls[$news->coverMediaId] ?? null, 'photoGallery' => $photoGallery]);
    }
}
