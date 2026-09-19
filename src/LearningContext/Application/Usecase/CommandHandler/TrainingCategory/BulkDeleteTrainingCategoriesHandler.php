<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler\TrainingCategory;
use Websymphonie\LearningContext\Application\Usecase\Command\TrainingCategory\BulkDeleteTrainingCategoriesCommand;
use Websymphonie\LearningContext\Domain\Repository\TrainingCategoryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
final readonly class BulkDeleteTrainingCategoriesHandler implements CommandHandler { public function __construct(private TrainingCategoryRepositoryInterface $repository) {} public function __invoke(BulkDeleteTrainingCategoriesCommand $command): int { $deleted = 0; foreach ($this->repository->findByIds(array_values(array_unique($command->ids))) as $category) { if ($this->repository->countTrainingUsage($category->id) === 0) { $this->repository->delete($category); ++$deleted; } } return $deleted; } }
