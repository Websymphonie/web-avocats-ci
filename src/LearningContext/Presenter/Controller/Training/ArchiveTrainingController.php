<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\Training;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\LearningContext\Application\Usecase\Command\ArchiveTrainingCommand;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/trainings', name: 'learning_admin_training_')]
#[IsGranted('LEARNING_TRAINING_PUBLISH')]
final class ArchiveTrainingController extends AbstractController
{
    #[Route('/{id}/archive', name: 'archive', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function __invoke(Request $request, int $id): Response
    {
        if (!$this->isCsrfTokenValid('training_archive_' . $id, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }
        try {
            $this->handleCommand(new ArchiveTrainingCommand($id));
            $this->flash()->success('Formation archivée.');
        } catch (UserFacingError $exception) {
            $this->flash()->errorFromException($exception);
        }

        return $this->redirectToRoute('learning_admin_training_list');
    }
}
