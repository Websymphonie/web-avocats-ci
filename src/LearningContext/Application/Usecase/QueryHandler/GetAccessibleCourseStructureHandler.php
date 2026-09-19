<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\QueryHandler;

use Websymphonie\LearningContext\Application\Service\TrainingAccessPolicyInterface;
use Websymphonie\LearningContext\Application\Usecase\Query\GetAccessibleCourseStructureQuery;
use Websymphonie\LearningContext\Domain\Model\CourseModuleStructure;
use Websymphonie\LearningContext\Domain\Model\CourseStructure;
use Websymphonie\LearningContext\Domain\Repository\CourseModuleRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonResourceRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetAccessibleCourseStructureHandler implements QueryHandler
{
    public function __construct(private TrainingAccessPolicyInterface $policy, private TrainingRepositoryInterface $trainings, private CourseModuleRepositoryInterface $modules, private LessonRepositoryInterface $lessons, private LessonResourceRepositoryInterface $resources) {}
    public function __invoke(GetAccessibleCourseStructureQuery $query): CourseStructure
    {
        $this->policy->assertCanAccess($query->trainingId, $query->userId);
        $training = $this->trainings->getById($query->trainingId);
        $modules = array_map(function ($module): CourseModuleStructure {
            $lessons = $this->lessons->listByModule($module->id);
            $counts = [];
            foreach ($lessons as $lesson) { $counts[$lesson->id] = $this->resources->countByLesson($lesson->id); }
            return new CourseModuleStructure($module, $lessons, $counts);
        }, $this->modules->listByTraining($training->id));
        return new CourseStructure($training, $modules);
    }
}
