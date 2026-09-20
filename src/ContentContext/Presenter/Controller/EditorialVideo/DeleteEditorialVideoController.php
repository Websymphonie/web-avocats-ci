<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Controller\EditorialVideo;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideo\DeleteEditorialVideoCommand;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;
#[Route('/videos', name: 'content_admin_video_')]
#[IsGranted('CONTENT_VIDEO_DELETE')]
#[HasGroupAccess(RoleGroupEnum::VIDEOS)]
final class DeleteEditorialVideoController extends AbstractController { #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\\d+'], methods: ['DELETE'])] public function __invoke(Request $request, int $id): Response { if (!$this->isCsrfTokenValid('delete' . $id, (string) $request->request->get('_token'))) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); } try { $this->handleCommand(new DeleteEditorialVideoCommand($id)); $this->flash()->success('Vidéo éditoriale supprimée.'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } return $this->redirectToRoute('content_admin_video_list'); } }
