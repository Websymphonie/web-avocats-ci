<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\PhotoGallery;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery\UpdatePhotoGalleryCommand;
use Websymphonie\ContentContext\Application\Usecase\Query\PhotoGallery\GetPhotoGalleryDetailsQuery;
use Websymphonie\ContentContext\Presenter\Form\PhotoGallery\PhotoGalleryFormType;
use Websymphonie\ContentContext\Presenter\Form\PhotoGallery\PhotoGalleryUploadImagesType;
use Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery\AddPhotoGalleryImagesCommand;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/galleries', name: 'content_admin_gallery_')]
#[IsGranted('CONTENT_GALLERY_MANAGE')]
final class UpdatePhotoGalleryController extends AbstractController
{
    public function __construct(private readonly MediaPublicUrlResolverInterface $urls) {}
    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function __invoke(Request $request, int $id): Response
    {
        $gallery = $this->handleQuery(new GetPhotoGalleryDetailsQuery($id));
        $command = new UpdatePhotoGalleryCommand($gallery->id, $gallery->title, $gallery->description, array_map(static fn ($tag): int => $tag->id, $gallery->tags));
        $form = $this->createForm(PhotoGalleryFormType::class, $command); $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $command->items = $this->itemMetadata($request->request->all('items'));
            try { $this->handleCommand($command); $this->flash()->success('Galerie photo modifiée.'); return $this->redirectToRoute('content_admin_gallery_edit', ['id' => $id]); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); }
        }
        $uploadForm = $this->createForm(PhotoGalleryUploadImagesType::class, new AddPhotoGalleryImagesCommand($gallery->id, []), ['action' => $this->generateUrl('content_admin_gallery_upload_images', ['id' => $gallery->id])]);
        return $this->render('content/admin/photo_gallery/edit.html.twig', ['gallery' => $gallery, 'form' => $form->createView(), 'uploadForm' => $uploadForm->createView(), 'mediaUrls' => $this->urls->resolveMany(array_map(static fn ($item): int => $item->mediaId, $gallery->items))]);
    }
    /**
     * @param array<array-key, mixed> $items
     * @return array<int, array{altText: string, caption: string|null}>
     */
    private function itemMetadata(array $items): array { $values = []; foreach ($items as $mediaId => $metadata) { if (is_array($metadata) && ctype_digit((string) $mediaId)) { $values[(int) $mediaId] = ['altText' => (string) ($metadata['altText'] ?? ''), 'caption' => isset($metadata['caption']) ? (string) $metadata['caption'] : null]; } } return $values; }
}
