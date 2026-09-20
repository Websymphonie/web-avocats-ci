<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\CourseModule;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\LearningContext\Application\Usecase\Command\Lesson\DeleteLessonCommand;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/trainings/{trainingId}/modules/{moduleId}/lessons', name: 'learning_admin_lesson_')]
#[IsGranted('LEARNING_TRAINING_MANAGE')]
#[HasGroupAccess(RoleGroupEnum::COURSE_MODULES)]
final class DeleteLessonController extends AbstractController
{
    #[Route('/{lessonId}/delete', name: 'delete', requirements: ['trainingId' => '\\d+', 'moduleId' => '\\d+', 'lessonId' => '\\d+'], methods: ['DELETE'])]
    public function __invoke(Request $request, int $trainingId, int $moduleId, int $lessonId): Response
    {
        if (!$this->isCsrfTokenValid('learning_lesson_delete_' . $trainingId . '_' . $lessonId, (string) $request->request->get('_token'))) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); }
        try { $this->handleCommand(new DeleteLessonCommand($trainingId, $moduleId, $lessonId)); $this->flash()->success('Leçon supprimée.'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); }
        return $this->redirectToRoute('learning_admin_training_show', ['id' => $trainingId]);
    }
}
