<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\CourseModule;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\LearningContext\Application\Usecase\Command\Lesson\ReorderLessonsCommand;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/trainings/{trainingId}/modules/{moduleId}/lessons', name: 'learning_admin_lesson_')]
#[IsGranted('LEARNING_TRAINING_MANAGE')]
final class ReorderLessonsController extends AbstractController
{
    #[Route('/reorder', name: 'reorder', requirements: ['trainingId' => '\\d+', 'moduleId' => '\\d+'], methods: ['POST'])]
    public function __invoke(Request $request, int $trainingId, int $moduleId): Response
    {
        if (!$this->isCsrfTokenValid('learning_lesson_reorder_' . $trainingId . '_' . $moduleId, (string) $request->request->get('_token'))) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); }
        $ids = array_values(array_map('intval', (array) $request->request->all('lessonIds')));
        try { $this->handleCommand(new ReorderLessonsCommand($trainingId, $moduleId, $ids)); $this->flash()->success('Ordre des leçons enregistré.'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); }
        return $this->redirectToRoute('learning_admin_training_show', ['id' => $trainingId]);
    }
}
