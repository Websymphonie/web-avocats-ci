<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\PhotoGallery;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery\AddPhotoGalleryImagesCommand;
use Websymphonie\ContentContext\Presenter\Form\PhotoGallery\PhotoGalleryUploadImagesType;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/galleries', name: 'content_admin_gallery_')]
#[IsGranted('CONTENT_GALLERY_MANAGE')]
#[HasGroupAccess(RoleGroupEnum::GALLERIES)]
final class AddPhotoGalleryImagesController extends AbstractController
{
    #[Route('/{id}/upload-images', name: 'upload_images', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function __invoke(Request $request, int $id): Response
    {
        $command = new AddPhotoGalleryImagesCommand($id, []); $form = $this->createForm(PhotoGalleryUploadImagesType::class, $command, ['action' => $this->generateUrl('content_admin_gallery_upload_images', ['id' => $id])]); $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { try { $this->handleCommand($command); $this->flash()->success('Images ajoutées à la galerie.'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } }
        return $this->redirectToRoute('content_admin_gallery_edit', ['id' => $id]);
    }
}
