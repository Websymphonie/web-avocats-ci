<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LearningContext\Application;

use PHPUnit\Framework\TestCase;
use Websymphonie\LearningContext\Application\Service\TrainingMediaUsageChecker;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;

final class TrainingMediaUsageCheckerTest extends TestCase
{
    public function testCoverMediaIsReportedAsUsed(): void
    {
        $repository = $this->createStub(TrainingRepositoryInterface::class);
        $repository->method('countMediaUsage')->willReturn(1);

        self::assertTrue((new TrainingMediaUsageChecker($repository))->isUsed(42));
    }

    public function testUnusedMediaIsReportedAsFree(): void
    {
        $repository = $this->createStub(TrainingRepositoryInterface::class);
        $repository->method('countMediaUsage')->willReturn(0);

        self::assertFalse((new TrainingMediaUsageChecker($repository))->isUsed(42));
    }
}
