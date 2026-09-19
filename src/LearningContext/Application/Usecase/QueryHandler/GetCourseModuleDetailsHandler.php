<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\QueryHandler;

use Websymphonie\LearningContext\Application\Service\CourseStructureGuard;
use Websymphonie\LearningContext\Application\Usecase\Query\GetCourseModuleDetailsQuery;
use Websymphonie\LearningContext\Domain\Model\CourseModuleStructure;
use Websymphonie\LearningContext\Domain\Repository\CourseModuleRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetCourseModuleDetailsHandler implements QueryHandler
{
    public function __construct(private TrainingRepositoryInterface $trainingRepository, private CourseModuleRepositoryInterface $moduleRepository, private LessonRepositoryInterface $lessonRepository, private CourseStructureGuard $guard) {}

    public function __invoke(GetCourseModuleDetailsQuery $query): CourseModuleStructure
    {
        $training = $this->trainingRepository->getById($query->trainingId);
        $this->guard->assertCourse($training);
        $module = $this->moduleRepository->getByIdForTraining($query->moduleId, $training->id);
        return new CourseModuleStructure($module, $this->lessonRepository->listByModule($module->id));
    }
}
