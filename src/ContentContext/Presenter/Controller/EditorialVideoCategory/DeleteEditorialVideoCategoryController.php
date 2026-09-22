<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Controller\EditorialVideoCategory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideoCategory\DeleteEditorialVideoCategoryCommand;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;
#[Route('/video-categories', name: 'content_admin_video_category_')]
#[IsGranted('CONTENT_VIDEO_CATEGORY_DELETE')]
#[HasGroupAccess(RoleGroupEnum::CATEGORY_VIDEOS)]
final class DeleteEditorialVideoCategoryController extends AbstractController { #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\\d+'], methods: ['DELETE'])] public function __invoke(Request $request, int $id): Response { if (!$this->isCsrfTokenValid('delete' . $id, (string) $request->request->get('_token'))) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); } try { $this->handleCommand(new DeleteEditorialVideoCategoryCommand($id)); $this->flash()->success('Catégorie de vidéo supprimée.'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } return $this->redirectToRoute('content_admin_video_category_list'); } }
