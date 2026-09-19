<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler\TrainingTag;
use Websymphonie\LearningContext\Application\Usecase\Command\TrainingTag\BulkDeleteTrainingTagsCommand;
use Websymphonie\LearningContext\Domain\Repository\TrainingTagRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
final readonly class BulkDeleteTrainingTagsHandler implements CommandHandler { public function __construct(private TrainingTagRepositoryInterface $repository) {} public function __invoke(BulkDeleteTrainingTagsCommand $command): int { $deleted = 0; foreach ($this->repository->findByIds(array_values(array_unique($command->ids))) as $tag) { if ($this->repository->countTrainingUsage($tag->id) === 0) { $this->repository->delete($tag); ++$deleted; } } return $deleted; } }
