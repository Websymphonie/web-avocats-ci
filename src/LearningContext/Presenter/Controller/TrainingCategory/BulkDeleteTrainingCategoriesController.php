<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Presenter\Controller\TrainingCategory;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\LearningContext\Application\Usecase\Command\TrainingCategory\BulkDeleteTrainingCategoriesCommand;
use Websymphonie\SharedContext\Presenter\AbstractController;
#[Route('/categories', name: 'learning_admin_category_')]
#[IsGranted('LEARNING_CATEGORY_DELETE')]
#[HasGroupAccess(RoleGroupEnum::CATEGORY_TRAININGS)]
final class BulkDeleteTrainingCategoriesController extends AbstractController { #[Route('/bulk-delete', name: 'bulk_delete', methods: ['POST'])] public function __invoke(Request $request): Response { if (!$this->isCsrfTokenValid('training-category-bulk-delete', (string) $request->request->get('_token'))) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); } $ids = array_values(array_unique(array_filter(array_map('intval', (array) $request->request->all('ids')), static fn (int $id): bool => $id > 0))); if ($ids !== []) { $deleted = $this->handleCommand(new BulkDeleteTrainingCategoriesCommand($ids)); $this->flash()->success(sprintf('%d catégorie(s) supprimée(s). Les catégories utilisées ont été conservées.', $deleted)); } return $this->redirectToRoute('learning_admin_category_list'); } }
