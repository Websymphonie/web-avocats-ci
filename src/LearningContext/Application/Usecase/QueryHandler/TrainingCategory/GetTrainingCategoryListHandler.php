<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Application\Usecase\QueryHandler\TrainingCategory;
use Websymphonie\LearningContext\Application\Usecase\Query\TrainingCategory\GetTrainingCategoryListQuery;
use Websymphonie\LearningContext\Domain\Model\TrainingCategoryListResult;
use Websymphonie\LearningContext\Domain\Repository\TrainingCategoryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;
final readonly class GetTrainingCategoryListHandler implements QueryHandler { public function __construct(private TrainingCategoryRepositoryInterface $repository) {} public function __invoke(GetTrainingCategoryListQuery $query): TrainingCategoryListResult { return $this->repository->list($query->search, max(1, $query->page), $query->limit); } }
