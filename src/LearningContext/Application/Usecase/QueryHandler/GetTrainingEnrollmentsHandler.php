<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\QueryHandler;

use Websymphonie\IdentityContext\Application\Service\User\UserDirectoryInterface;
use Websymphonie\LearningContext\Application\Usecase\Query\GetTrainingEnrollmentsQuery;
use Websymphonie\LearningContext\Domain\Model\EnrollmentListItem;
use Websymphonie\LearningContext\Domain\Model\EnrollmentListView;
use Websymphonie\LearningContext\Domain\Model\EnrollmentUser;
use Websymphonie\LearningContext\Domain\Model\CourseProgress;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Repository\LessonProgressRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\EnrollmentRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetTrainingEnrollmentsHandler implements QueryHandler
{
    public function __construct(private TrainingRepositoryInterface $trainings, private EnrollmentRepositoryInterface $enrollments, private UserDirectoryInterface $users, private LessonRepositoryInterface $lessons, private LessonProgressRepositoryInterface $progress) {}
    public function __invoke(GetTrainingEnrollmentsQuery $query): EnrollmentListView
    {
        $training = $this->trainings->getById($query->trainingId);
        $userIds = null;
        if ($query->search !== null && trim($query->search) !== '') { $userIds = array_map(static fn ($user): int => $user->id, $this->users->search($query->search)); }
        $result = $this->enrollments->listByTraining($training->id, $query->status, $query->source, $userIds, max(1, $query->page), $query->limit);
        $directory = [];
        foreach ($this->users->getByIds(array_map(static fn ($item): int => $item->userId, $result->items)) as $user) { $directory[$user->id] = new EnrollmentUser($user->id, $user->name, $user->email, $user->enabled); }
        $progressByEnrollment = [];
        $totalLessons = 0;
        if ($training->type === TrainingType::COURSE) {
            $totalLessons = $this->lessons->countByTraining($training->id);
            $progressByEnrollment = $this->progress->summarizeByEnrollmentIds(array_map(static fn ($item): int => $item->id, $result->items), $totalLessons);
        }
        return new EnrollmentListView(array_map(function ($item) use ($directory, $progressByEnrollment, $training, $totalLessons): EnrollmentListItem {
            $progress = $training->type === TrainingType::COURSE ? ($progressByEnrollment[$item->id] ?? CourseProgress::empty($item->id, $totalLessons)) : null;
            return new EnrollmentListItem($item, $directory[$item->userId] ?? null, $progress);
        }, $result->items), $result->totalItemCount, $result->page, $result->itemNumberPerPage);
    }
}
