<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Controller\EditorialVideo;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideo\ArchiveEditorialVideoCommand;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;
#[Route('/videos', name: 'content_admin_video_')]
#[IsGranted('CONTENT_VIDEO_PUBLISH')]
#[HasGroupAccess(RoleGroupEnum::VIDEOS)]
final class ArchiveEditorialVideoController extends AbstractController { #[Route('/{id}/archive', name: 'archive', requirements: ['id' => '\\d+'], methods: ['POST'])] public function __invoke(Request $request, int $id): Response { if (!$this->isCsrfTokenValid('video_archive_' . $id, (string) $request->request->get('_token'))) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); } try { $this->handleCommand(new ArchiveEditorialVideoCommand($id)); $this->flash()->success('Vidéo éditoriale archivée.'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } return $this->redirectToRoute('content_admin_video_list'); } }
