<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LearningContext\Application\Usecase\CommandHandler;

use PHPUnit\Framework\TestCase;
use Websymphonie\LearningContext\Application\Service\CourseStructureGuard;
use Websymphonie\LearningContext\Application\Usecase\Command\CourseModule\ReorderCourseModulesCommand;
use Websymphonie\LearningContext\Application\Usecase\CommandHandler\CourseModule\ReorderCourseModulesHandler;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Exception\InvalidCourseStructureException;
use Websymphonie\LearningContext\Domain\Model\CourseModule;
use Websymphonie\LearningContext\Domain\Model\Training;
use Websymphonie\LearningContext\Domain\Repository\CourseModuleRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;

final class ReorderCourseModulesHandlerTest extends TestCase
{
    public function testRejectsDuplicateOrIncompleteIds(): void
    {
        $training = $this->training();
        $trainingRepository = $this->createMock(TrainingRepositoryInterface::class);
        $trainingRepository->method('getById')->willReturn($training);
        $moduleRepository = $this->createMock(CourseModuleRepositoryInterface::class);
        $moduleRepository->method('listByTraining')->willReturn([
            new CourseModule(1, 'one', 10, 'One', position: 1),
            new CourseModule(2, 'two', 10, 'Two', position: 2),
        ]);
        $moduleRepository->expects(self::never())->method('reorder');

        $handler = new ReorderCourseModulesHandler($trainingRepository, $moduleRepository, new CourseStructureGuard());

        $this->expectException(InvalidCourseStructureException::class);
        $handler(new ReorderCourseModulesCommand(10, [1, 1]));
    }

    public function testPersistsACompleteOrder(): void
    {
        $training = $this->training();
        $trainingRepository = $this->createMock(TrainingRepositoryInterface::class);
        $trainingRepository->method('getById')->willReturn($training);
        $moduleRepository = $this->createMock(CourseModuleRepositoryInterface::class);
        $moduleRepository->method('listByTraining')->willReturn([
            new CourseModule(1, 'one', 10, 'One', position: 1),
            new CourseModule(2, 'two', 10, 'Two', position: 2),
        ]);
        $moduleRepository->expects(self::once())->method('reorder')->with(10, [2, 1]);

        $handler = new ReorderCourseModulesHandler($trainingRepository, $moduleRepository, new CourseStructureGuard());

        $handler(new ReorderCourseModulesCommand(10, [2, 1]));
    }

    private function training(): Training
    {
        return new Training(10, 'training', TrainingType::COURSE, 'Cours', 'cours', 'Résumé', '<p>Description</p>', \Websymphonie\LearningContext\Domain\Enum\TrainingVisibility::PUBLIC, \Websymphonie\LearningContext\Domain\Enum\TrainingAccessType::FREE);
    }
}
