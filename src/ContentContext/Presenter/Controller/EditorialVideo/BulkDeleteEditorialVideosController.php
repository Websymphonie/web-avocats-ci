<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Controller\EditorialVideo;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideo\BulkDeleteEditorialVideosCommand;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;
#[Route('/videos', name: 'content_admin_video_')]
#[IsGranted('CONTENT_VIDEO_DELETE')]
#[HasGroupAccess(RoleGroupEnum::VIDEOS)]
final class BulkDeleteEditorialVideosController extends AbstractController { #[Route('/bulk-delete', name: 'bulk_delete', methods: ['POST'])] public function __invoke(Request $request): Response { if (!$this->isCsrfTokenValid('editorial-video-bulk-delete', (string) $request->request->get('_token'))) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); } $ids = array_values(array_unique(array_filter(array_map('intval', (array) $request->request->all('ids')), static fn (int $id): bool => $id > 0))); if ($ids !== []) { try { $this->handleCommand(new BulkDeleteEditorialVideosCommand($ids)); $this->flash()->success(sprintf('%d vidéo(s) éditoriale(s) supprimée(s).', count($ids))); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } } return $this->redirectToRoute('content_admin_video_list'); } }
