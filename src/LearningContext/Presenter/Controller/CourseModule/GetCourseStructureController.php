<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\CourseModule;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\LearningContext\Application\Usecase\Query\GetCourseStructureQuery;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/trainings', name: 'learning_admin_course_structure_')]
#[IsGranted('LEARNING_TRAINING_VIEW')]
final class GetCourseStructureController extends AbstractController
{
    #[Route('/{trainingId}/structure', name: 'show', requirements: ['trainingId' => '\\d+'], methods: ['GET'])]
    public function __invoke(int $trainingId): Response
    {
        return $this->render('learning/admin/course_structure/index.html.twig', ['structure' => $this->handleQuery(new GetCourseStructureQuery($trainingId))]);
    }
}
