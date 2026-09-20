<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\Training;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\LearningContext\Application\Usecase\Command\BulkDeleteTrainingsCommand;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/trainings', name: 'learning_admin_training_')]
#[IsGranted('LEARNING_TRAINING_DELETE')]
#[HasGroupAccess(RoleGroupEnum::TRAININGS)]
final class BulkDeleteTrainingsController extends AbstractController
{
    #[Route('/bulk-delete', name: 'bulk_delete', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('training-bulk-delete', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $request->request->all('ids')), static fn (int $id): bool => $id > 0)));
        if ($ids !== []) {
            try {
                $this->handleCommand(new BulkDeleteTrainingsCommand($ids));
                $this->flash()->success(sprintf('%d formation(s) supprimée(s).', count($ids)));
            } catch (UserFacingError $exception) {
                $this->flash()->errorFromException($exception);
            }
        }

        return $this->redirectToRoute('learning_admin_training_list');
    }
}
