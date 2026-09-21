<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\QueryHandler;

use Websymphonie\LearningContext\Application\Service\TrainingAccessPolicyInterface;
use Websymphonie\LearningContext\Application\Usecase\Query\GetMemberLiveSessionQuery;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Exception\LiveTrainingDetailsNotFoundException;
use Websymphonie\LearningContext\Domain\Model\MemberLiveSession;
use Websymphonie\LearningContext\Domain\Repository\TrainingCategoryRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetMemberLiveSessionHandler implements QueryHandler
{
    public function __construct(
        private TrainingAccessPolicyInterface $policy,
        private TrainingRepositoryInterface $trainings,
        private TrainingCategoryRepositoryInterface $categories,
    ) {
    }

    public function __invoke(GetMemberLiveSessionQuery $query): MemberLiveSession
    {
        $training = $this->trainings->getByUuid($query->trainingUuid);
        $this->policy->assertCanAccess($training->id, $query->userId);

        if ($training->type !== TrainingType::LIVE || $training->liveDetails === null) {
            throw LiveTrainingDetailsNotFoundException::withTrainingId($training->id);
        }

        $category = $training->categoryIds !== []
            ? ($this->categories->findByIds([$training->categoryIds[0]])[0] ?? null)
            : null;

        return new MemberLiveSession(
            trainingUuid: $training->uuid,
            title: $training->title,
            coverMediaId: $training->coverMediaId,
            categoryName: $category?->name,
            startsAt: $training->liveDetails->startsAt,
            endsAt: $training->liveDetails->endsAt,
            deliveryMode: $training->liveDetails->deliveryMode,
            location: $training->liveDetails->location,
            canJoin: $training->liveDetails->deliveryMode->requiresJoinUrl()
                && $training->liveDetails->hasJoinUrl(),
            streamProvider: $training->liveDetails->streamProvider,
            externalStreamId: $training->liveDetails->externalStreamId,
            videoEmbedUrl: $training->liveDetails->streamEmbedUrl(),
        );
    }
}
