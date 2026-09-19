<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\CourseModule;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\LearningContext\Application\Usecase\Command\Lesson\UpdateLessonCommand;
use Websymphonie\LearningContext\Application\Usecase\Command\Lesson\AddLessonResourcesCommand;
use Websymphonie\LearningContext\Application\Usecase\Query\GetLessonDetailsQuery;
use Websymphonie\LearningContext\Presenter\Form\CourseModule\LessonFormType;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/trainings/{trainingId}/modules/{moduleId}/lessons', name: 'learning_admin_lesson_')]
#[IsGranted('LEARNING_TRAINING_MANAGE')]
final class UpdateLessonController extends AbstractController
{
    #[Route('/{lessonId}/edit', name: 'edit', requirements: ['trainingId' => '\\d+', 'moduleId' => '\\d+', 'lessonId' => '\\d+'], methods: ['GET', 'POST'])]
    public function __invoke(Request $request, int $trainingId, int $moduleId, int $lessonId): Response
    {
        $details = $this->handleQuery(new GetLessonDetailsQuery($trainingId, $moduleId, $lessonId));
        $lesson = $details->lesson;
        $command = new UpdateLessonCommand($trainingId, $moduleId, $lesson->id, $lesson->title, $lesson->summary, $lesson->content, $lesson->videoUrl);
        $form = $this->createForm(LessonFormType::class, $command)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try { $lesson = $this->handleCommand($command); if ($command->resourceFiles !== []) { $this->handleCommand(new AddLessonResourcesCommand($trainingId, $moduleId, $lesson->id, $command->resourceFiles)); } $this->flash()->success('Leçon modifiée.'); return $this->redirectToRoute('learning_admin_training_show', ['id' => $trainingId]); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); }
        }
        return $this->render('learning/admin/course_structure/lesson_form.html.twig', ['form' => $form->createView(), 'trainingId' => $trainingId, 'module' => $details->module, 'lesson' => $lesson, 'resources' => $details->resources, 'pageTitle' => 'Modifier la leçon']);
    }
}
