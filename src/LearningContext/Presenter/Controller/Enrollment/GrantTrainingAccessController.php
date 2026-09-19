<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\Enrollment;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\LearningContext\Application\Usecase\Command\GrantTrainingAccessCommand;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/trainings/{trainingId}/enrollments', name: 'learning_admin_enrollment_')]
#[IsGranted('LEARNING_ENROLLMENT_MANAGE')]
final class GrantTrainingAccessController extends AbstractController
{
    #[Route('/grant', name: 'grant', requirements: ['trainingId' => '\\d+'], methods: ['POST'])]
    public function __invoke(Request $request, int $trainingId): Response
    {
        if (!$this->isCsrfTokenValid('learning_enrollment_grant_' . $trainingId, (string) $request->request->get('_token'))) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); }
        try { $this->handleCommand(new GrantTrainingAccessCommand($trainingId, (int) $request->request->get('userId'))); $this->flash()->success('Accès accordé.'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); }
        return $this->redirectToRoute('learning_admin_enrollment_list', ['trainingId' => $trainingId]);
    }
}
