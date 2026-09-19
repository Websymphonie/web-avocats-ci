<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler\CourseModule;

use Websymphonie\LearningContext\Application\Service\CourseStructureGuard;
use Websymphonie\LearningContext\Application\Usecase\Command\CourseModule\CreateCourseModuleCommand;
use Websymphonie\LearningContext\Domain\Model\CourseModule;
use Websymphonie\LearningContext\Domain\Repository\CourseModuleRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class CreateCourseModuleHandler implements CommandHandler
{
    public function __construct(private TrainingRepositoryInterface $trainingRepository, private CourseModuleRepositoryInterface $repository, private CourseStructureGuard $guard) {}

    public function __invoke(CreateCourseModuleCommand $command): CourseModule
    {
        $training = $this->trainingRepository->getById($command->trainingId);
        $this->guard->assertCourse($training);
        $module = new CourseModule(0, '', $training->id, trim($command->title), trim($command->description), $this->repository->countByTraining($training->id) + 1);
        return $this->repository->save($module);
    }
}
