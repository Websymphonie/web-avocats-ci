<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler\CourseModule;

use Websymphonie\LearningContext\Application\Service\CourseStructureGuard;
use Websymphonie\LearningContext\Application\Usecase\Command\CourseModule\UpdateCourseModuleCommand;
use Websymphonie\LearningContext\Domain\Model\CourseModule;
use Websymphonie\LearningContext\Domain\Repository\CourseModuleRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class UpdateCourseModuleHandler implements CommandHandler
{
    public function __construct(private TrainingRepositoryInterface $trainingRepository, private CourseModuleRepositoryInterface $repository, private CourseStructureGuard $guard) {}

    public function __invoke(UpdateCourseModuleCommand $command): CourseModule
    {
        $training = $this->trainingRepository->getById($command->trainingId);
        $this->guard->assertCourse($training);
        $module = $this->repository->getByIdForTraining($command->id, $training->id);
        $module->update($command->title, $command->description);
        return $this->repository->save($module);
    }
}
