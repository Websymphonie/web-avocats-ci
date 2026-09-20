<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\CourseModule;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\LearningContext\Application\Usecase\Command\Lesson\CreateLessonCommand;
use Websymphonie\LearningContext\Application\Usecase\Command\Lesson\AddLessonResourcesCommand;
use Websymphonie\LearningContext\Application\Usecase\Query\GetCourseModuleDetailsQuery;
use Websymphonie\LearningContext\Presenter\Form\CourseModule\LessonFormType;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/trainings/{trainingId}/modules/{moduleId}/lessons', name: 'learning_admin_lesson_')]
#[IsGranted('LEARNING_TRAINING_MANAGE')]
#[HasGroupAccess(RoleGroupEnum::COURSE_MODULES)]
final class CreateLessonController extends AbstractController
{
    #[Route('/new', name: 'new', requirements: ['trainingId' => '\\d+', 'moduleId' => '\\d+'], methods: ['GET', 'POST'])]
    public function __invoke(Request $request, int $trainingId, int $moduleId): Response
    {
        $module = $this->handleQuery(new GetCourseModuleDetailsQuery($trainingId, $moduleId));
        $command = new CreateLessonCommand($trainingId, $moduleId);
        $form = $this->createForm(LessonFormType::class, $command)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try { $lesson = $this->handleCommand($command); if ($command->resourceFiles !== []) { $this->handleCommand(new AddLessonResourcesCommand($trainingId, $moduleId, $lesson->id, $command->resourceFiles)); } $this->flash()->success('Leçon ajoutée au module.'); return $this->redirectToRoute('learning_admin_training_show', ['id' => $trainingId]); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); }
        }
        return $this->render('learning/admin/course_structure/lesson_form.html.twig', ['form' => $form->createView(), 'trainingId' => $trainingId, 'module' => $module->module, 'resources' => [], 'pageTitle' => 'Nouvelle leçon']);
    }
}
