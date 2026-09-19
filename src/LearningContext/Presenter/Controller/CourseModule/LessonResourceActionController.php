<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\CourseModule;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\LearningContext\Application\Usecase\Command\Lesson\RemoveLessonResourceCommand;
use Websymphonie\LearningContext\Application\Usecase\Command\Lesson\ReorderLessonResourcesCommand;
use Websymphonie\LearningContext\Application\Usecase\Command\Lesson\UpdateLessonResourceCommand;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/trainings/{trainingId}/modules/{moduleId}/lessons/{lessonId}/resources', name: 'learning_admin_lesson_resource_')]
#[IsGranted('LEARNING_TRAINING_MANAGE')]
final class LessonResourceActionController extends AbstractController
{
    #[Route('/{resourceId}/rename', name: 'rename', requirements: ['trainingId' => '\\d+', 'moduleId' => '\\d+', 'lessonId' => '\\d+', 'resourceId' => '\\d+'], methods: ['POST'])]
    public function rename(Request $request, int $trainingId, int $moduleId, int $lessonId, int $resourceId): Response
    {
        $this->assertToken($request, 'learning_lesson_resource_rename_' . $trainingId . '_' . $resourceId);
        try { $this->handleCommand(new UpdateLessonResourceCommand($trainingId, $moduleId, $lessonId, $resourceId, (string) $request->request->get('title'))); $this->flash()->success('Nom de la ressource modifié.'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); }
        return $this->redirectToEdit($trainingId, $moduleId, $lessonId);
    }

    #[Route('/{resourceId}/delete', name: 'delete', requirements: ['trainingId' => '\\d+', 'moduleId' => '\\d+', 'lessonId' => '\\d+', 'resourceId' => '\\d+'], methods: ['DELETE'])]
    public function delete(Request $request, int $trainingId, int $moduleId, int $lessonId, int $resourceId): Response
    {
        $this->assertToken($request, 'learning_lesson_resource_delete_' . $trainingId . '_' . $resourceId);
        try { $this->handleCommand(new RemoveLessonResourceCommand($trainingId, $moduleId, $lessonId, $resourceId)); $this->flash()->success('Ressource retirée de la leçon.'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); }
        return $this->redirectToEdit($trainingId, $moduleId, $lessonId);
    }

    #[Route('/reorder', name: 'reorder', requirements: ['trainingId' => '\\d+', 'moduleId' => '\\d+', 'lessonId' => '\\d+'], methods: ['POST'])]
    public function reorder(Request $request, int $trainingId, int $moduleId, int $lessonId): Response
    {
        $this->assertToken($request, 'learning_lesson_resource_reorder_' . $trainingId . '_' . $lessonId);
        $ids = array_values(array_map('intval', (array) $request->request->all('resourceIds')));
        try { $this->handleCommand(new ReorderLessonResourcesCommand($trainingId, $moduleId, $lessonId, $ids)); $this->flash()->success('Ordre des ressources enregistré.'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); }
        return $this->redirectToEdit($trainingId, $moduleId, $lessonId);
    }

    private function assertToken(Request $request, string $id): void
    {
        if (!$this->isCsrfTokenValid($id, (string) $request->request->get('_token'))) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); }
    }

    private function redirectToEdit(int $trainingId, int $moduleId, int $lessonId): Response
    {
        return $this->redirectToRoute('learning_admin_lesson_edit', ['trainingId' => $trainingId, 'moduleId' => $moduleId, 'lessonId' => $lessonId]);
    }
}
