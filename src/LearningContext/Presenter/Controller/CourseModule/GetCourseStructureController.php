<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\CourseModule;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\LearningContext\Application\Usecase\Query\GetCourseStructureQuery;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/trainings', name: 'learning_admin_course_structure_')]
#[IsGranted('LEARNING_TRAINING_VIEW')]
#[HasGroupAccess(RoleGroupEnum::COURSE_MODULES)]
final class GetCourseStructureController extends AbstractController
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/{trainingId}/structure', name: 'show', requirements: ['trainingId' => '\\d+'], methods: ['GET'])]
    public function __invoke(int $trainingId): Response
    {
        return $this->render('learning/admin/course_structure/index.html.twig', ['structure' => $this->handleQuery(new GetCourseStructureQuery($trainingId))]);
    }
}
