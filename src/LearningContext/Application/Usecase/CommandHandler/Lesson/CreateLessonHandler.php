<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler\Lesson;

use Websymphonie\LearningContext\Application\Service\CourseStructureGuard;
use Websymphonie\LearningContext\Application\Service\YouTubeSourceParser;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\LearningContext\Application\Usecase\Command\Lesson\CreateLessonCommand;
use Websymphonie\LearningContext\Domain\Model\Lesson;
use Websymphonie\LearningContext\Domain\Repository\CourseModuleRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class CreateLessonHandler implements CommandHandler
{
    public function __construct(private TrainingRepositoryInterface $trainingRepository, private CourseModuleRepositoryInterface $moduleRepository, private LessonRepositoryInterface $repository, private RichTextSanitizerInterface $sanitizer, private CourseStructureGuard $guard, private YouTubeSourceParser $videoParser) {}

    public function __invoke(CreateLessonCommand $command): Lesson
    {
        $training = $this->trainingRepository->getById($command->trainingId);
        $this->guard->assertCourse($training);
        $module = $this->moduleRepository->getByIdForTraining($command->moduleId, $training->id);
        $lesson = new Lesson(0, '', $module->id, trim($command->title), $command->summary, $this->repository->countByModule($module->id) + 1);
        $lesson->update($command->title, $command->summary, $this->sanitizer->sanitize($command->content), $this->videoParser->parseLessonReference($command->videoReference, $command->videoProvider));
        return $this->repository->save($lesson);
    }
}
