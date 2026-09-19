<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\QueryHandler;

use Websymphonie\LearningContext\Application\Service\TrainingAccessPolicyInterface;
use Websymphonie\LearningContext\Application\Usecase\Query\GetAccessibleLiveJoinDetailsQuery;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Exception\InvalidLiveTrainingDetailsException;
use Websymphonie\LearningContext\Domain\Exception\LiveTrainingDetailsNotFoundException;
use Websymphonie\LearningContext\Domain\Model\LiveTrainingDetails;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetAccessibleLiveJoinDetailsHandler implements QueryHandler
{
    public function __construct(private TrainingRepositoryInterface $trainings, private TrainingAccessPolicyInterface $policy) {}

    public function __invoke(GetAccessibleLiveJoinDetailsQuery $query): LiveTrainingDetails
    {
        $training = $this->trainings->getByUuid($query->trainingUuid);
        $this->policy->assertCanAccess($training->id, $query->userId);
        if ($training->type !== TrainingType::LIVE || $training->liveDetails === null) {
            throw LiveTrainingDetailsNotFoundException::withTrainingId($training->id);
        }
        if (!$training->liveDetails->deliveryMode->requiresJoinUrl() || !$training->liveDetails->hasJoinUrl()) {
            throw new InvalidLiveTrainingDetailsException('Cette session ne propose pas de lien de connexion externe.');
        }

        return $training->liveDetails;
    }
}
