<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\PhotoGallery;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Query\PhotoGallery\GetPhotoGalleryListQuery;
use Websymphonie\ContentContext\Domain\Enum\PhotoGalleryStatus;
use Websymphonie\ContentContext\Presenter\Form\PhotoGallery\PhotoGalleryFilterType;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/galleries', name: 'content_admin_gallery_')]
#[IsGranted('CONTENT_GALLERY_VIEW')]
final class GetPhotoGalleryListController extends AbstractController
{
    public function __construct(private readonly MediaPublicUrlResolverInterface $urls) {}
    #[Route('', name: 'list', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $query = new GetPhotoGalleryListQuery(page: max(1, $request->query->getInt('page', 1)));
        $form = $this->createForm(PhotoGalleryFilterType::class, $query, ['method' => 'GET', 'action' => $this->generateUrl('content_admin_gallery_list')]);
        $form->handleRequest($request);
        $galleries = $this->handleQuery(new GetPhotoGalleryListQuery($query->search ?: null, $query->status instanceof PhotoGalleryStatus ? $query->status : null, $query->tagId ?: null, $query->page, 20));
        return $this->render('content/admin/photo_gallery/index.html.twig', ['galleries' => $galleries, 'filterForm' => $form->createView(), 'mediaUrls' => $this->urls->resolveMany(array_values(array_filter(array_map(static fn ($gallery): ?int => $gallery->coverMediaId, $galleries->items))))]);
    }
}
