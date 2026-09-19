<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\PhotoGallery;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Application\Usecase\Query\PhotoGallery\GetPhotoGalleryDetailsQuery;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/galleries', name: 'content_admin_gallery_')]
#[IsGranted('CONTENT_GALLERY_VIEW')]
final class GetPhotoGalleryDetailsController extends AbstractController
{
    public function __construct(private readonly RichTextSanitizerInterface $sanitizer, private readonly MediaPublicUrlResolverInterface $urls) {}
    #[Route('/{id}', name: 'show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function __invoke(int $id): Response { $gallery = $this->handleQuery(new GetPhotoGalleryDetailsQuery($id)); return $this->render('content/admin/photo_gallery/show.html.twig', ['gallery' => $gallery, 'safeDescription' => $this->sanitizer->sanitize($gallery->description), 'mediaUrls' => $this->urls->resolveMany(array_map(static fn ($item): int => $item->mediaId, $gallery->items))]); }
}
