<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler\CourseModule;

use Websymphonie\LearningContext\Application\Service\CourseStructureGuard;
use Websymphonie\LearningContext\Application\Usecase\Command\CourseModule\DeleteCourseModuleCommand;
use Websymphonie\LearningContext\Domain\Repository\CourseModuleRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class DeleteCourseModuleHandler implements CommandHandler
{
    public function __construct(private TrainingRepositoryInterface $trainingRepository, private CourseModuleRepositoryInterface $repository, private CourseStructureGuard $guard) {}

    public function __invoke(DeleteCourseModuleCommand $command): void
    {
        $training = $this->trainingRepository->getById($command->trainingId);
        $this->guard->assertCourse($training);
        $module = $this->repository->getByIdForTraining($command->id, $training->id);
        $this->repository->delete($module);
        $this->repository->reorder($training->id, array_map(static fn ($item): int => $item->id, $this->repository->listByTraining($training->id)));
    }
}
