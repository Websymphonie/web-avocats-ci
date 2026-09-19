<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\QueryHandler;

use Websymphonie\LearningContext\Application\Service\CourseStructureGuard;
use Websymphonie\LearningContext\Application\Usecase\Query\GetLessonDetailsQuery;
use Websymphonie\LearningContext\Domain\Model\Lesson;
use Websymphonie\LearningContext\Domain\Repository\CourseModuleRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetLessonDetailsHandler implements QueryHandler
{
    public function __construct(private TrainingRepositoryInterface $trainingRepository, private CourseModuleRepositoryInterface $moduleRepository, private LessonRepositoryInterface $lessonRepository, private CourseStructureGuard $guard) {}

    public function __invoke(GetLessonDetailsQuery $query): Lesson
    {
        $training = $this->trainingRepository->getById($query->trainingId);
        $this->guard->assertCourse($training);
        $module = $this->moduleRepository->getByIdForTraining($query->moduleId, $training->id);
        return $this->lessonRepository->getByIdForModule($query->lessonId, $module->id);
    }
}
