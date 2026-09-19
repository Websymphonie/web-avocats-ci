<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LearningContext\Application\Usecase\CommandHandler;

use PHPUnit\Framework\TestCase;
use Websymphonie\LearningContext\Application\Service\CourseStructureGuard;
use Websymphonie\LearningContext\Application\Usecase\Command\Lesson\ReorderLessonResourcesCommand;
use Websymphonie\LearningContext\Application\Usecase\CommandHandler\Lesson\ReorderLessonResourcesHandler;
use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Enum\TrainingVisibility;
use Websymphonie\LearningContext\Domain\Exception\InvalidCourseStructureException;
use Websymphonie\LearningContext\Domain\Model\CourseModule;
use Websymphonie\LearningContext\Domain\Model\Lesson;
use Websymphonie\LearningContext\Domain\Model\LessonResource;
use Websymphonie\LearningContext\Domain\Model\Training;
use Websymphonie\LearningContext\Domain\Repository\CourseModuleRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonResourceRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;

final class ReorderLessonResourcesHandlerTest extends TestCase
{
    public function testRejectsAnIncompleteOrder(): void
    {
        $resources = [new LessonResource(1, 'one', 20, 31, 'One', 1), new LessonResource(2, 'two', 20, 32, 'Two', 2)];
        $resourceRepository = $this->createMock(LessonResourceRepositoryInterface::class);
        $resourceRepository->method('listByLesson')->willReturn($resources);
        $resourceRepository->expects(self::never())->method('reorder');

        $handler = new ReorderLessonResourcesHandler($this->trainingRepository(), $this->moduleRepository(), $this->lessonRepository(), $resourceRepository, new CourseStructureGuard());

        $this->expectException(InvalidCourseStructureException::class);
        $handler(new ReorderLessonResourcesCommand(10, 11, 20, [1]));
    }

    public function testPersistsACompleteOrder(): void
    {
        $resourceRepository = $this->createMock(LessonResourceRepositoryInterface::class);
        $resourceRepository->method('listByLesson')->willReturn([new LessonResource(1, 'one', 20, 31, 'One', 1), new LessonResource(2, 'two', 20, 32, 'Two', 2)]);
        $resourceRepository->expects(self::once())->method('reorder')->with(20, [2, 1]);

        $handler = new ReorderLessonResourcesHandler($this->trainingRepository(), $this->moduleRepository(), $this->lessonRepository(), $resourceRepository, new CourseStructureGuard());
        $handler(new ReorderLessonResourcesCommand(10, 11, 20, [2, 1]));
    }

    private function trainingRepository(): TrainingRepositoryInterface
    {
        $repository = $this->createMock(TrainingRepositoryInterface::class);
        $repository->method('getById')->willReturn(new Training(10, 'training', TrainingType::COURSE, 'Cours', 'cours', 'Résumé', '<p>Description</p>', TrainingVisibility::PUBLIC, TrainingAccessType::FREE));
        return $repository;
    }

    private function moduleRepository(): CourseModuleRepositoryInterface
    {
        $repository = $this->createMock(CourseModuleRepositoryInterface::class);
        $repository->method('getByIdForTraining')->willReturn(new CourseModule(11, 'module', 10, 'Module', '', 1));
        return $repository;
    }

    private function lessonRepository(): LessonRepositoryInterface
    {
        $repository = $this->createMock(LessonRepositoryInterface::class);
        $repository->method('getByIdForModule')->willReturn(new Lesson(20, 'lesson', 11, 'Leçon'));
        return $repository;
    }
}
