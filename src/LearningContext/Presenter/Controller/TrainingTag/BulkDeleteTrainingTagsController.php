<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Presenter\Controller\TrainingTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\LearningContext\Application\Usecase\Command\TrainingTag\BulkDeleteTrainingTagsCommand;
use Websymphonie\SharedContext\Presenter\AbstractController;
#[Route('/tags', name: 'learning_admin_tag_')]
#[IsGranted('LEARNING_TAG_DELETE')]
final class BulkDeleteTrainingTagsController extends AbstractController { #[Route('/bulk-delete', name: 'bulk_delete', methods: ['POST'])] public function __invoke(Request $request): Response { if (!$this->isCsrfTokenValid('training-tag-bulk-delete', (string) $request->request->get('_token'))) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); } $ids = array_values(array_unique(array_filter(array_map('intval', (array) $request->request->all('ids')), static fn (int $id): bool => $id > 0))); if ($ids !== []) { $deleted = $this->handleCommand(new BulkDeleteTrainingTagsCommand($ids)); $this->flash()->success(sprintf('%d tag(s) supprimé(s). Les tags utilisés ont été conservés.', $deleted)); } return $this->redirectToRoute('learning_admin_tag_list'); } }
