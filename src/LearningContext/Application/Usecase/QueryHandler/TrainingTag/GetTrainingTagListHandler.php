<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Application\Usecase\QueryHandler\TrainingTag;
use Websymphonie\LearningContext\Application\Usecase\Query\TrainingTag\GetTrainingTagListQuery;
use Websymphonie\LearningContext\Domain\Model\TrainingTagListResult;
use Websymphonie\LearningContext\Domain\Repository\TrainingTagRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;
final readonly class GetTrainingTagListHandler implements QueryHandler { public function __construct(private TrainingTagRepositoryInterface $repository) {} public function __invoke(GetTrainingTagListQuery $query): TrainingTagListResult { return $this->repository->list($query->search, max(1, $query->page), $query->limit); } }
