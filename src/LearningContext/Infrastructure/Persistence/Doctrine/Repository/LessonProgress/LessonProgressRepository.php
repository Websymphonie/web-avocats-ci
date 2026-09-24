<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Repository\LessonProgress;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;
use Websymphonie\LearningContext\Domain\Enum\LessonProgressStatus;
use Websymphonie\LearningContext\Domain\Model\CourseProgress;
use Websymphonie\LearningContext\Domain\Model\LessonProgress;
use Websymphonie\LearningContext\Domain\Repository\LessonProgressRepositoryInterface;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\LessonProgress\LessonProgressEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Factory\LessonProgressFactory;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/** @extends ServiceEntityRepository<LessonProgressEntity> */
final class LessonProgressRepository extends ServiceEntityRepository implements LessonProgressRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private readonly ManagersInterface $manager, private readonly LessonProgressFactory $factory)
    {
        parent::__construct($registry, LessonProgressEntity::class);
    }

    public function save(LessonProgress $progress): LessonProgress
    {
        $entity = $progress->id > 0 ? $this->find($progress->id) : null;
        $entity = $this->factory->toEntity($progress, $entity instanceof LessonProgressEntity ? $entity : null);
        DbLogListener::disable();
        try { $this->manager->execute($entity, $progress->id > 0 ? DbActionEnum::EDIT : DbActionEnum::NEW); } finally { DbLogListener::enable(); }
        return $this->factory->fromEntity($entity);
    }

    public function findByEnrollmentAndLesson(int $enrollmentId, int $lessonId): ?LessonProgress
    {
        $entity = $this->findOneBy(['enrollmentId' => $enrollmentId, 'lessonId' => $lessonId]);
        return $entity instanceof LessonProgressEntity ? $this->factory->fromEntity($entity) : null;
    }

    public function listByEnrollment(int $enrollmentId): array
    {
        $entities = $this->createQueryBuilder('progress')->where('progress.enrollmentId = :enrollmentId')->setParameter('enrollmentId', $enrollmentId)->orderBy('progress.lessonId', 'ASC')->getQuery()->getResult();
        return array_map(fn (LessonProgressEntity $entity): LessonProgress => $this->factory->fromEntity($entity), $entities);
    }

    public function countByLesson(int $lessonId): int
    {
        return (int) $this->createQueryBuilder('progress')->select('COUNT(progress.id)')->where('progress.lessonId = :lessonId')->setParameter('lessonId', $lessonId)->getQuery()->getSingleScalarResult();
    }

    /** @param list<int> $enrollmentIds @return array<int, CourseProgress> */
    public function summarizeByEnrollmentIds(array $enrollmentIds, int $totalLessons): array
    {
        return $this->summarizeByEnrollmentIdsWithTotalLessons(array_fill_keys($enrollmentIds, $totalLessons));
    }

    /** @param array<int, int> $totalLessonsByEnrollmentId @return array<int, CourseProgress> */
    public function summarizeByEnrollmentIdsWithTotalLessons(array $totalLessonsByEnrollmentId): array
    {
        if ($totalLessonsByEnrollmentId === []) { return []; }
        $enrollmentIds = array_keys($totalLessonsByEnrollmentId);
        $rows = $this->getEntityManager()->getConnection()->executeQuery(
            'SELECT progress.enrollment_id, COUNT(progress.id) AS started_lessons, SUM(CASE WHEN progress.status = ? THEN 1 ELSE 0 END) AS completed_lessons, MAX(progress.last_accessed_at) AS last_activity_at, (SELECT lesson.uuid FROM lesson_progress AS last_progress INNER JOIN lesson ON lesson.id = last_progress.lesson_id WHERE last_progress.enrollment_id = progress.enrollment_id ORDER BY last_progress.last_accessed_at DESC, last_progress.id DESC LIMIT 1) AS last_accessed_lesson_uuid, (SELECT last_progress.status FROM lesson_progress AS last_progress WHERE last_progress.enrollment_id = progress.enrollment_id ORDER BY last_progress.last_accessed_at DESC, last_progress.id DESC LIMIT 1) AS last_accessed_lesson_status FROM lesson_progress AS progress WHERE progress.enrollment_id IN (?) GROUP BY progress.enrollment_id',
            [LessonProgressStatus::COMPLETED->value, $enrollmentIds],
            ['string', ArrayParameterType::INTEGER],
        )->fetchAllAssociative();
        $result = [];
        foreach ($totalLessonsByEnrollmentId as $enrollmentId => $totalLessons) {
            $result[$enrollmentId] = CourseProgress::empty($enrollmentId, $totalLessons);
        }
        foreach ($rows as $row) {
            $enrollmentId = (int) $row['enrollment_id'];
            $completed = (int) $row['completed_lessons'];
            $totalLessons = $totalLessonsByEnrollmentId[$enrollmentId] ?? 0;
            $result[$enrollmentId] = new CourseProgress(
                $enrollmentId,
                $totalLessons,
                (int) $row['started_lessons'],
                $completed,
                $totalLessons > 0 ? (int) round($completed / $totalLessons * 100) : 0,
                $row['last_activity_at'] !== null ? new \DateTimeImmutable((string) $row['last_activity_at']) : null,
                $row['last_accessed_lesson_uuid'] !== null ? Uuid::fromBinary((string) $row['last_accessed_lesson_uuid'])->toRfc4122() : null,
                $row['last_accessed_lesson_status'] === LessonProgressStatus::COMPLETED->value,
            );
        }
        return $result;
    }
}
