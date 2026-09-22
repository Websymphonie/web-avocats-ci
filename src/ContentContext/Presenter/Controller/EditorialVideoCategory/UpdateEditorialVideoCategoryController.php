<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Controller\EditorialVideoCategory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideoCategory\UpdateEditorialVideoCategoryCommand;
use Websymphonie\ContentContext\Domain\Repository\EditorialVideoCategoryRepositoryInterface;
use Websymphonie\ContentContext\Presenter\Form\EditorialVideoCategory\EditorialVideoCategoryFormType;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;
#[Route('/video-categories', name: 'content_admin_video_category_')]
#[IsGranted('CONTENT_VIDEO_CATEGORY_MANAGE')]
#[HasGroupAccess(RoleGroupEnum::CATEGORY_VIDEOS)]
final class UpdateEditorialVideoCategoryController extends AbstractController { public function __construct(private readonly EditorialVideoCategoryRepositoryInterface $repository) {} #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])] public function __invoke(Request $request, int $id): Response { $category = $this->repository->getById($id); $command = new UpdateEditorialVideoCategoryCommand($category->id, $category->name, $category->description); $form = $this->createForm(EditorialVideoCategoryFormType::class, $command); $form->handleRequest($request); if ($form->isSubmitted() && $form->isValid()) { try { $this->handleCommand($command); $this->flash()->success('Catégorie de vidéo modifiée.'); return $this->redirectToRoute('content_admin_video_category_list'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } } return $this->render('content/admin/editorial_video_category/form.html.twig', ['form' => $form->createView(), 'title' => 'Modifier la catégorie de vidéos']); } }
