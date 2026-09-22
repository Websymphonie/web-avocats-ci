<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Controller\EditorialVideoCategory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideoCategory\CreateEditorialVideoCategoryCommand;
use Websymphonie\ContentContext\Presenter\Form\EditorialVideoCategory\EditorialVideoCategoryFormType;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;
#[Route('/video-categories', name: 'content_admin_video_category_')]
#[IsGranted('CONTENT_VIDEO_CATEGORY_MANAGE')]
#[HasGroupAccess(RoleGroupEnum::CATEGORY_VIDEOS)]
final class CreateEditorialVideoCategoryController extends AbstractController { #[Route('/new', name: 'new', methods: ['GET', 'POST'])] public function __invoke(Request $request): Response { $command = new CreateEditorialVideoCategoryCommand(); $form = $this->createForm(EditorialVideoCategoryFormType::class, $command); $form->handleRequest($request); if ($form->isSubmitted() && $form->isValid()) { try { $this->handleCommand($command); $this->flash()->success('Catégorie de vidéo créée.'); return $this->redirectToRoute('content_admin_video_category_list'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } } return $this->render('content/admin/editorial_video_category/form.html.twig', ['form' => $form->createView(), 'title' => 'Nouvelle catégorie de vidéos']); } }
