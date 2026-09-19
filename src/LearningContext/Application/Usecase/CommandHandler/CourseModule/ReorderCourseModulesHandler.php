<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler\CourseModule;

use Websymphonie\LearningContext\Application\Service\CourseStructureGuard;
use Websymphonie\LearningContext\Application\Usecase\Command\CourseModule\ReorderCourseModulesCommand;
use Websymphonie\LearningContext\Domain\Exception\InvalidCourseStructureException;
use Websymphonie\LearningContext\Domain\Repository\CourseModuleRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class ReorderCourseModulesHandler implements CommandHandler
{
    public function __construct(private TrainingRepositoryInterface $trainingRepository, private CourseModuleRepositoryInterface $repository, private CourseStructureGuard $guard) {}

    public function __invoke(ReorderCourseModulesCommand $command): void
    {
        $training = $this->trainingRepository->getById($command->trainingId);
        $this->guard->assertCourse($training);
        if (count($command->moduleIds) !== count(array_unique($command->moduleIds))) {
            throw new InvalidCourseStructureException('L’ordre des modules contient des doublons.');
        }
        $modules = $this->repository->listByTraining($training->id);
        $knownIds = array_map(static fn ($module): int => $module->id, $modules);
        if (count($knownIds) !== count($command->moduleIds) || array_diff($knownIds, $command->moduleIds) !== [] || array_diff($command->moduleIds, $knownIds) !== []) {
            throw new InvalidCourseStructureException('L’ordre des modules est incomplet ou contient un module étranger.');
        }
        $this->repository->reorder($training->id, $command->moduleIds);
    }
}
