<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\CourseModule;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\LearningContext\Application\Usecase\Command\CourseModule\CreateCourseModuleCommand;
use Websymphonie\LearningContext\Presenter\Form\CourseModule\CourseModuleFormType;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/trainings/{trainingId}/modules', name: 'learning_admin_course_module_')]
#[IsGranted('LEARNING_TRAINING_MANAGE')]
final class CreateCourseModuleController extends AbstractController
{
    #[Route('/new', name: 'new', requirements: ['trainingId' => '\\d+'], methods: ['GET', 'POST'])]
    public function __invoke(Request $request, int $trainingId): Response
    {
        $command = new CreateCourseModuleCommand($trainingId);
        $form = $this->createForm(CourseModuleFormType::class, $command)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try { $this->handleCommand($command); $this->flash()->success('Module ajouté au programme.'); return $this->redirectToRoute('learning_admin_training_show', ['id' => $trainingId]); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); }
        }
        return $this->render('learning/admin/course_structure/module_form.html.twig', ['form' => $form->createView(), 'trainingId' => $trainingId, 'pageTitle' => 'Nouveau module']);
    }
}
