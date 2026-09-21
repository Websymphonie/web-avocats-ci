<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\QueryHandler;

use Websymphonie\LearningContext\Application\Service\TrainingLearnerEligibilityInterface;
use Websymphonie\LearningContext\Application\Usecase\Query\GetMemberTrainingSummariesQuery;
use Websymphonie\LearningContext\Domain\Enum\EnrollmentStatus;
use Websymphonie\LearningContext\Domain\Enum\TrainingStatus;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Model\CourseProgress;
use Websymphonie\LearningContext\Domain\Model\MemberTrainingSummary;
use Websymphonie\LearningContext\Domain\Repository\EnrollmentRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonProgressRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingCategoryRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetMemberTrainingSummariesHandler implements QueryHandler
{
    public function __construct(
        private TrainingLearnerEligibilityInterface $eligibility,
        private EnrollmentRepositoryInterface $enrollments,
        private TrainingRepositoryInterface $trainings,
        private TrainingCategoryRepositoryInterface $categories,
        private LessonRepositoryInterface $lessons,
        private LessonProgressRepositoryInterface $progress,
    ) {
    }

    /** @return list<MemberTrainingSummary> */
    public function __invoke(GetMemberTrainingSummariesQuery $query): array
    {
        if (!$this->eligibility->isEligible($query->userId)) {
            return [];
        }

        $enrollments = $this->enrollments->listByUser($query->userId, EnrollmentStatus::ACTIVE);
        if ($enrollments === []) {
            return [];
        }

        $trainingIds = array_values(array_unique(array_map(static fn ($enrollment): int => $enrollment->trainingId, $enrollments)));
        $trainingById = [];
        foreach ($this->trainings->findByIds($trainingIds) as $training) {
            if ($training->status === TrainingStatus::PUBLISHED) {
                $trainingById[$training->id] = $training;
            }
        }

        if ($trainingById === []) {
            return [];
        }

        $categoryIds = [];
        foreach ($trainingById as $training) {
            $categoryIds = array_merge($categoryIds, $training->categoryIds);
        }
        $categoryById = [];
        foreach ($this->categories->findByIds(array_values(array_unique($categoryIds))) as $category) {
            $categoryById[$category->id] = $category;
        }

        $courseTrainingIds = array_values(array_filter(
            array_keys($trainingById),
            static fn (int $trainingId): bool => $trainingById[$trainingId]->type === TrainingType::COURSE,
        ));
        $lessonCounts = $this->lessons->countByTrainingIds($courseTrainingIds);
        $totalLessonsByEnrollmentId = [];
        foreach ($enrollments as $enrollment) {
            if (isset($trainingById[$enrollment->trainingId]) && $trainingById[$enrollment->trainingId]->type === TrainingType::COURSE) {
                $totalLessonsByEnrollmentId[$enrollment->id] = $lessonCounts[$enrollment->trainingId] ?? 0;
            }
        }
        $progressByEnrollmentId = $this->progress->summarizeByEnrollmentIdsWithTotalLessons($totalLessonsByEnrollmentId);

        $summaries = [];
        foreach ($enrollments as $enrollment) {
            $training = $trainingById[$enrollment->trainingId] ?? null;
            if ($training === null) {
                continue;
            }

            $categoryId = $training->categoryIds[0] ?? null;
            $summaries[] = new MemberTrainingSummary(
                enrollment: $enrollment,
                training: $training,
                category: $categoryId !== null ? ($categoryById[$categoryId] ?? null) : null,
                progress: $training->type === TrainingType::COURSE
                    ? ($progressByEnrollmentId[$enrollment->id] ?? CourseProgress::empty($enrollment->id, $lessonCounts[$training->id] ?? 0))
                    : null,
            );
        }

        return $summaries;
    }
}
