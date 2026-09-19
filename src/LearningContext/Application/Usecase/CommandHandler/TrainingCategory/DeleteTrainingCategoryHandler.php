<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler\TrainingCategory;
use Websymphonie\LearningContext\Application\Usecase\Command\TrainingCategory\DeleteTrainingCategoryCommand;
use Websymphonie\LearningContext\Domain\Exception\TrainingCategoryInUseException;
use Websymphonie\LearningContext\Domain\Repository\TrainingCategoryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
final readonly class DeleteTrainingCategoryHandler implements CommandHandler { public function __construct(private TrainingCategoryRepositoryInterface $repository) {} public function __invoke(DeleteTrainingCategoryCommand $command): void { $category = $this->repository->getById($command->id); $count = $this->repository->countTrainingUsage($category->id); if ($count > 0) { throw TrainingCategoryInUseException::withCount($count); } $this->repository->delete($category); } }
