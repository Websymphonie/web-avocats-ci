<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Controller\EditorialVideoCategory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideoCategory\BulkDeleteEditorialVideoCategoriesCommand;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;
#[Route('/video-categories', name: 'content_admin_video_category_')]
#[IsGranted('CONTENT_VIDEO_CATEGORY_DELETE')]
#[HasGroupAccess(RoleGroupEnum::CATEGORY_VIDEOS)]
final class BulkDeleteEditorialVideoCategoriesController extends AbstractController { #[Route('/bulk-delete', name: 'bulk_delete', methods: ['POST'])] public function __invoke(Request $request): Response { if (!$this->isCsrfTokenValid('editorial-video-category-bulk-delete', (string) $request->request->get('_token'))) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); } $ids = array_values(array_unique(array_filter(array_map('intval', (array) $request->request->all('ids')), static fn (int $id): bool => $id > 0))); if ($ids !== []) { try { $this->handleCommand(new BulkDeleteEditorialVideoCategoriesCommand($ids)); $this->flash()->success(sprintf('%d catégorie(s) supprimée(s).', count($ids))); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } } return $this->redirectToRoute('content_admin_video_category_list'); } }
