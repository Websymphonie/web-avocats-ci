<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler\TrainingTag;
use Websymphonie\LearningContext\Application\Usecase\Command\TrainingTag\DeleteTrainingTagCommand;
use Websymphonie\LearningContext\Domain\Exception\TrainingTagInUseException;
use Websymphonie\LearningContext\Domain\Repository\TrainingTagRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
final readonly class DeleteTrainingTagHandler implements CommandHandler { public function __construct(private TrainingTagRepositoryInterface $repository) {} public function __invoke(DeleteTrainingTagCommand $command): void { $tag = $this->repository->getById($command->id); $count = $this->repository->countTrainingUsage($tag->id); if ($count > 0) { throw TrainingTagInUseException::withCount($count); } $this->repository->delete($tag); } }
