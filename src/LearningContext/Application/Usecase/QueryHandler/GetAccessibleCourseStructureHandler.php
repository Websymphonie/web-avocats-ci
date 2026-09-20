<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\QueryHandler;

use Websymphonie\LearningContext\Application\Service\TrainingAccessPolicyInterface;
use Websymphonie\LearningContext\Application\Service\CourseStructureGuard;
use Websymphonie\LearningContext\Application\Usecase\Query\GetAccessibleCourseStructureQuery;
use Websymphonie\LearningContext\Domain\Model\CourseModuleStructure;
use Websymphonie\LearningContext\Domain\Model\CourseStructure;
use Websymphonie\LearningContext\Domain\Repository\CourseModuleRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\EnrollmentRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonProgressRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonResourceRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetAccessibleCourseStructureHandler implements QueryHandler
{
    public function __construct(private TrainingAccessPolicyInterface $policy, private TrainingRepositoryInterface $trainings, private CourseModuleRepositoryInterface $modules, private LessonRepositoryInterface $lessons, private LessonResourceRepositoryInterface $resources, private EnrollmentRepositoryInterface $enrollments, private LessonProgressRepositoryInterface $progress, private CourseStructureGuard $guard) {}
    public function __invoke(GetAccessibleCourseStructureQuery $query): CourseStructure
    {
        $this->policy->assertCanAccess($query->trainingId, $query->userId);
        $training = $this->trainings->getById($query->trainingId);
        $this->guard->assertCourse($training);
        $enrollment = $this->enrollments->findByTrainingAndUser($training->id, $query->userId);
        if ($enrollment === null) { throw new \LogicException('Une inscription active était attendue après le contrôle d’accès.'); }
        $progressByLesson = [];
        foreach ($this->progress->listByEnrollment($enrollment->id) as $lessonProgress) { $progressByLesson[$lessonProgress->lessonId] = $lessonProgress->status; }
        $modules = array_map(function ($module) use ($progressByLesson): CourseModuleStructure {
            $lessons = $this->lessons->listByModule($module->id);
            $counts = [];
            foreach ($lessons as $lesson) { $counts[$lesson->id] = $this->resources->countByLesson($lesson->id); }
            $statuses = [];
            foreach ($lessons as $lesson) { if (isset($progressByLesson[$lesson->id])) { $statuses[$lesson->id] = $progressByLesson[$lesson->id]; } }
            return new CourseModuleStructure($module, $lessons, $counts, $statuses);
        }, $this->modules->listByTraining($training->id));
        return new CourseStructure($training, $modules);
    }
}
