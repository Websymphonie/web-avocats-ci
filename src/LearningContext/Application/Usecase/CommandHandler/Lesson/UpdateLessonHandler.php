<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler\Lesson;

use Websymphonie\LearningContext\Application\Service\CourseStructureGuard;
use Websymphonie\LearningContext\Application\Usecase\Command\Lesson\UpdateLessonCommand;
use Websymphonie\LearningContext\Domain\Model\Lesson;
use Websymphonie\LearningContext\Domain\Repository\CourseModuleRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class UpdateLessonHandler implements CommandHandler
{
    public function __construct(private TrainingRepositoryInterface $trainingRepository, private CourseModuleRepositoryInterface $moduleRepository, private LessonRepositoryInterface $repository, private CourseStructureGuard $guard) {}

    public function __invoke(UpdateLessonCommand $command): Lesson
    {
        $training = $this->trainingRepository->getById($command->trainingId);
        $this->guard->assertCourse($training);
        $module = $this->moduleRepository->getByIdForTraining($command->moduleId, $training->id);
        $lesson = $this->repository->getByIdForModule($command->id, $module->id);
        $lesson->update($command->title, $command->summary);
        return $this->repository->save($lesson);
    }
}
