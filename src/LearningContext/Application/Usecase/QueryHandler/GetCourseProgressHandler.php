<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\QueryHandler;

use Websymphonie\LearningContext\Application\Service\CourseStructureGuard;
use Websymphonie\LearningContext\Application\Service\TrainingAccessPolicyInterface;
use Websymphonie\LearningContext\Application\Usecase\Query\GetCourseProgressQuery;
use Websymphonie\LearningContext\Domain\Model\CourseProgress;
use Websymphonie\LearningContext\Domain\Repository\EnrollmentRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonProgressRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetCourseProgressHandler implements QueryHandler
{
    public function __construct(private TrainingAccessPolicyInterface $policy, private TrainingRepositoryInterface $trainings, private EnrollmentRepositoryInterface $enrollments, private LessonRepositoryInterface $lessons, private LessonProgressRepositoryInterface $progress, private CourseStructureGuard $guard) {}

    public function __invoke(GetCourseProgressQuery $query): CourseProgress
    {
        $this->policy->assertCanAccess($query->trainingId, $query->userId);
        $training = $this->trainings->getById($query->trainingId);
        $this->guard->assertCourse($training);
        $enrollment = $this->enrollments->findByTrainingAndUser($training->id, $query->userId);
        if ($enrollment === null) { throw new \LogicException('Une inscription active était attendue après le contrôle d’accès.'); }
        $totalLessons = $this->lessons->countByTraining($training->id);
        return $this->progress->summarizeByEnrollmentIds([$enrollment->id], $totalLessons)[$enrollment->id] ?? CourseProgress::empty($enrollment->id, $totalLessons);
    }
}
