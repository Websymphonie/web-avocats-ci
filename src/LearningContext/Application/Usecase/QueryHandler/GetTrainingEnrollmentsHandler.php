<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\QueryHandler;

use Websymphonie\IdentityContext\Application\Service\User\UserDirectoryInterface;
use Websymphonie\LearningContext\Application\Usecase\Query\GetTrainingEnrollmentsQuery;
use Websymphonie\LearningContext\Domain\Model\EnrollmentListItem;
use Websymphonie\LearningContext\Domain\Model\EnrollmentListView;
use Websymphonie\LearningContext\Domain\Model\EnrollmentUser;
use Websymphonie\LearningContext\Domain\Repository\EnrollmentRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetTrainingEnrollmentsHandler implements QueryHandler
{
    public function __construct(private TrainingRepositoryInterface $trainings, private EnrollmentRepositoryInterface $enrollments, private UserDirectoryInterface $users) {}
    public function __invoke(GetTrainingEnrollmentsQuery $query): EnrollmentListView
    {
        $training = $this->trainings->getById($query->trainingId);
        $userIds = null;
        if ($query->search !== null && trim($query->search) !== '') { $userIds = array_map(static fn ($user): int => $user->id, $this->users->search($query->search)); }
        $result = $this->enrollments->listByTraining($training->id, $query->status, $query->source, $userIds, max(1, $query->page), $query->limit);
        $directory = [];
        foreach ($this->users->getByIds(array_map(static fn ($item): int => $item->userId, $result->items)) as $user) { $directory[$user->id] = new EnrollmentUser($user->id, $user->name, $user->email, $user->enabled); }
        return new EnrollmentListView(array_map(fn ($item): EnrollmentListItem => new EnrollmentListItem($item, $directory[$item->userId] ?? null), $result->items), $result->totalItemCount, $result->page, $result->itemNumberPerPage);
    }
}
