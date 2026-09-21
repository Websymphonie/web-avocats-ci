<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\QueryHandler;

use Websymphonie\LearningContext\Application\Service\CourseStructureGuard;
use Websymphonie\LearningContext\Application\Service\TrainingAccessPolicyInterface;
use Websymphonie\LearningContext\Application\Usecase\Query\GetMemberCoursePlayerQuery;
use Websymphonie\LearningContext\Domain\Enum\EnrollmentStatus;
use Websymphonie\LearningContext\Domain\Exception\LessonNotFoundException;
use Websymphonie\LearningContext\Domain\Model\CourseProgress;
use Websymphonie\LearningContext\Domain\Model\Enrollment;
use Websymphonie\LearningContext\Domain\Model\MemberCoursePlayer;
use Websymphonie\LearningContext\Domain\Model\MemberCoursePlayerLesson;
use Websymphonie\LearningContext\Domain\Model\MemberCoursePlayerModule;
use Websymphonie\LearningContext\Domain\Model\TrainingCategory;
use Websymphonie\LearningContext\Domain\Repository\CourseModuleRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\EnrollmentRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonProgressRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonResourceRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingCategoryRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetMemberCoursePlayerHandler implements QueryHandler
{
    public function __construct(
        private TrainingAccessPolicyInterface $policy,
        private TrainingRepositoryInterface $trainings,
        private CourseModuleRepositoryInterface $modules,
        private LessonRepositoryInterface $lessons,
        private LessonResourceRepositoryInterface $resources,
        private EnrollmentRepositoryInterface $enrollments,
        private LessonProgressRepositoryInterface $progress,
        private TrainingCategoryRepositoryInterface $categories,
        private CourseStructureGuard $guard,
    ) {
    }

    public function __invoke(GetMemberCoursePlayerQuery $query): MemberCoursePlayer
    {
        $training = $this->trainings->getByUuid($query->trainingUuid);
        $this->policy->assertCanAccess($training->id, $query->userId);
        $this->guard->assertCourse($training);

        $enrollment = $this->enrollments->findByTrainingAndUser($training->id, $query->userId);
        if (!$enrollment instanceof Enrollment || $enrollment->status !== EnrollmentStatus::ACTIVE) {
            throw new \LogicException('Une inscription active était attendue après le contrôle d’accès.');
        }

        $modules = $this->modules->listByTraining($training->id);
        $lessons = $this->lessons->listByTraining($training->id);
        $lessonsByModule = [];
        foreach ($lessons as $lesson) { $lessonsByModule[$lesson->moduleId][] = $lesson; }

        $progressByLesson = [];
        foreach ($this->progress->listByEnrollment($enrollment->id) as $lessonProgress) {
            $progressByLesson[$lessonProgress->lessonId] = $lessonProgress;
        }

        $playerLessons = [];
        $playerModules = [];
        foreach ($modules as $module) {
            $moduleLessons = [];
            foreach ($lessonsByModule[$module->id] ?? [] as $lesson) {
                $playerLesson = new MemberCoursePlayerLesson($lesson, $progressByLesson[$lesson->id] ?? null);
                $moduleLessons[] = $playerLesson;
                $playerLessons[] = $playerLesson;
            }
            $playerModules[] = new MemberCoursePlayerModule($module, $moduleLessons);
        }

        if ($playerLessons === []) {
            throw new LessonNotFoundException('Cette formation ne contient aucune leçon accessible.');
        }

        $activeIndex = $this->findActiveLessonIndex($playerLessons, $query->lessonUuid);
        $activeLesson = $playerLessons[$activeIndex];
        $activeLesson = new MemberCoursePlayerLesson($activeLesson->lesson, $activeLesson->progress, $this->resources->listByLesson($activeLesson->lesson->id));
        $playerLessons[$activeIndex] = $activeLesson;

        $totalLessons = count($playerLessons);
        $progressSummary = $this->progress->summarizeByEnrollmentIdsWithTotalLessons([$enrollment->id => $totalLessons]);
        $courseProgress = $progressSummary[$enrollment->id] ?? CourseProgress::empty($enrollment->id, $totalLessons);
        $category = $this->findPrimaryCategory($training->categoryIds);

        return new MemberCoursePlayer(
            training: $training,
            category: $category,
            enrollment: $enrollment,
            modules: $playerModules,
            progress: $courseProgress,
            activeLesson: $activeLesson,
            previousLesson: $activeIndex > 0 ? $playerLessons[$activeIndex - 1] : null,
            nextLesson: $activeIndex < $totalLessons - 1 ? $playerLessons[$activeIndex + 1] : null,
        );
    }

    /** @param list<MemberCoursePlayerLesson> $lessons */
    private function findActiveLessonIndex(array $lessons, ?string $lessonUuid): int
    {
        if ($lessonUuid !== null) {
            foreach ($lessons as $index => $lesson) {
                if ($lesson->lesson->uuid === $lessonUuid) { return $index; }
            }
            throw LessonNotFoundException::withUuid($lessonUuid);
        }

        foreach ($lessons as $index => $lesson) {
            if (!$lesson->isCompleted()) { return $index; }
        }

        return 0;
    }

    /** @param list<int> $categoryIds */
    private function findPrimaryCategory(array $categoryIds): ?TrainingCategory
    {
        $categoryId = $categoryIds[0] ?? null;
        if ($categoryId === null) { return null; }

        return $this->categories->findByIds([$categoryId])[0] ?? null;
    }
}
