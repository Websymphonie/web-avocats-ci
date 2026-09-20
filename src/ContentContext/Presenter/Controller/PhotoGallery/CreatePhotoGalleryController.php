<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\PhotoGallery;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery\CreatePhotoGalleryCommand;
use Websymphonie\ContentContext\Presenter\Form\PhotoGallery\PhotoGalleryCreateFormType;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/galleries', name: 'content_admin_gallery_')]
#[IsGranted('CONTENT_GALLERY_MANAGE')]
#[HasGroupAccess(RoleGroupEnum::GALLERIES)]
final class CreatePhotoGalleryController extends AbstractController
{
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $command = new CreatePhotoGalleryCommand(); $form = $this->createForm(PhotoGalleryCreateFormType::class, $command); $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { try { $gallery = $this->handleCommand($command); $this->flash()->success('Galerie photo créée en brouillon.'); return $this->redirectToRoute('content_admin_gallery_edit', ['id' => $gallery->id]); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } }
        return $this->render('content/admin/photo_gallery/create.html.twig', ['form' => $form->createView()]);
    }
}
