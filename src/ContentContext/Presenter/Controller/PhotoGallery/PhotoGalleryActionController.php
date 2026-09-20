<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\PhotoGallery;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery\ArchivePhotoGalleryCommand;
use Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery\BulkDeletePhotoGalleriesCommand;
use Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery\DeletePhotoGalleryCommand;
use Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery\PublishPhotoGalleryCommand;
use Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery\RemovePhotoGalleryItemCommand;
use Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery\ReorderPhotoGalleryItemsCommand;
use Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery\SetPhotoGalleryCoverCommand;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/galleries', name: 'content_admin_gallery_')]
#[HasGroupAccess(RoleGroupEnum::GALLERIES)]
final class PhotoGalleryActionController extends AbstractController
{
    #[Route('/{id}/publish', name: 'publish', requirements: ['id' => '\\d+'], methods: ['POST'])]
    #[IsGranted('CONTENT_GALLERY_PUBLISH')]
    public function publish(Request $request, int $id): Response
    {
        if (!$this->validCsrf($request, 'gallery_publish_' . $id)) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); }
        try { $this->handleCommand(new PublishPhotoGalleryCommand($id)); $this->flash()->success('Galerie photo publiée.'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); return $this->redirectToRoute('content_admin_gallery_edit', ['id' => $id]); }
        return $this->redirectToRoute('content_admin_gallery_list');
    }

    #[Route('/{id}/archive', name: 'archive', requirements: ['id' => '\\d+'], methods: ['POST'])]
    #[IsGranted('CONTENT_GALLERY_PUBLISH')]
    public function archive(Request $request, int $id): Response
    {
        if (!$this->validCsrf($request, 'gallery_archive_' . $id)) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); }
        try { $this->handleCommand(new ArchivePhotoGalleryCommand($id)); $this->flash()->success('Galerie photo archivée.'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); return $this->redirectToRoute('content_admin_gallery_edit', ['id' => $id]); }
        return $this->redirectToRoute('content_admin_gallery_list');
    }

    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\\d+'], methods: ['DELETE'])]
    #[IsGranted('CONTENT_GALLERY_DELETE')]
    public function delete(Request $request, int $id): Response
    {
        if (!$this->validCsrf($request, 'delete' . $id)) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); }
        try { $this->handleCommand(new DeletePhotoGalleryCommand($id)); $this->flash()->success('Galerie photo supprimée. Les médias sont conservés jusqu’à leur suppression explicite.'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); }
        return $this->redirectToRoute('content_admin_gallery_list');
    }

    #[Route('/bulk-delete', name: 'bulk_delete', methods: ['POST'])]
    #[IsGranted('CONTENT_GALLERY_DELETE')]
    public function bulkDelete(Request $request): Response
    {
        if (!$this->validCsrf($request, 'photo-gallery-bulk-delete')) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); }
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $request->request->all('ids')), static fn (int $id): bool => $id > 0)));
        if ($ids !== []) { try { $this->handleCommand(new BulkDeletePhotoGalleriesCommand($ids)); $this->flash()->success(sprintf('%d galerie(s) photo supprimée(s).', count($ids))); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } }
        return $this->redirectToRoute('content_admin_gallery_list');
    }

    #[Route('/{id}/items/{mediaId}/remove', name: 'remove_item', requirements: ['id' => '\\d+', 'mediaId' => '\\d+'], methods: ['POST'])]
    #[IsGranted('CONTENT_GALLERY_MANAGE')]
    public function removeItem(Request $request, int $id, int $mediaId): Response
    {
        if (!$this->validCsrf($request, 'gallery_remove_' . $id . '_' . $mediaId)) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); }
        try { $this->handleCommand(new RemovePhotoGalleryItemCommand($id, $mediaId)); $this->flash()->success('Image retirée de la galerie.'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); }
        return $this->redirectToRoute('content_admin_gallery_edit', ['id' => $id]);
    }

    #[Route('/{id}/items/reorder', name: 'reorder', requirements: ['id' => '\\d+'], methods: ['POST'])]
    #[IsGranted('CONTENT_GALLERY_MANAGE')]
    public function reorder(Request $request, int $id): Response
    {
        if (!$this->validCsrf($request, 'gallery_reorder_' . $id)) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); }
        try { $this->handleCommand(new ReorderPhotoGalleryItemsCommand($id, array_values(array_map('intval', (array) $request->request->all('mediaIds'))))); $this->flash()->success('Ordre des images enregistré.'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); }
        return $this->redirectToRoute('content_admin_gallery_edit', ['id' => $id]);
    }

    #[Route('/{id}/cover/{mediaId}', name: 'set_cover', requirements: ['id' => '\\d+', 'mediaId' => '\\d+'], methods: ['POST'])]
    #[IsGranted('CONTENT_GALLERY_MANAGE')]
    public function setCover(Request $request, int $id, int $mediaId): Response
    {
        if (!$this->validCsrf($request, 'gallery_cover_' . $id . '_' . $mediaId)) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); }
        try { $this->handleCommand(new SetPhotoGalleryCoverCommand($id, $mediaId)); $this->flash()->success('Image de couverture définie.'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); }
        return $this->redirectToRoute('content_admin_gallery_edit', ['id' => $id]);
    }

    private function validCsrf(Request $request, string $id): bool { return $this->isCsrfTokenValid($id, (string) $request->request->get('_token')); }
}
