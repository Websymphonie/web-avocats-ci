<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\QueryHandler;

use Websymphonie\LearningContext\Application\Service\CourseStructureGuard;
use Websymphonie\LearningContext\Application\Usecase\Query\GetCourseStructureQuery;
use Websymphonie\LearningContext\Domain\Model\CourseModuleStructure;
use Websymphonie\LearningContext\Domain\Model\CourseStructure;
use Websymphonie\LearningContext\Domain\Repository\CourseModuleRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonResourceRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetCourseStructureHandler implements QueryHandler
{
    public function __construct(private TrainingRepositoryInterface $trainingRepository, private CourseModuleRepositoryInterface $moduleRepository, private LessonRepositoryInterface $lessonRepository, private LessonResourceRepositoryInterface $resourceRepository, private CourseStructureGuard $guard) {}

    public function __invoke(GetCourseStructureQuery $query): CourseStructure
    {
        $training = $this->trainingRepository->getById($query->trainingId);
        $this->guard->assertCourse($training);
        $modules = array_map(function ($module): CourseModuleStructure {
            $lessons = $this->lessonRepository->listByModule($module->id);
            $resourceCounts = [];
            foreach ($lessons as $lesson) { $resourceCounts[$lesson->id] = $this->resourceRepository->countByLesson($lesson->id); }
            return new CourseModuleStructure($module, $lessons, $resourceCounts);
        }, $this->moduleRepository->listByTraining($training->id));
        return new CourseStructure($training, $modules);
    }
}
