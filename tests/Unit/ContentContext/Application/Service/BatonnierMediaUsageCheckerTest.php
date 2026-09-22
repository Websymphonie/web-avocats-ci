<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Unit\ContentContext\Application\Service;

use PHPUnit\Framework\TestCase;
use Websymphonie\ContentContext\Application\Service\BatonnierMediaUsageChecker;
use Websymphonie\ContentContext\Domain\Repository\BatonnierMandateRepositoryInterface;

final class BatonnierMediaUsageCheckerTest extends TestCase
{
    public function testUsedPortraitIsReported(): void
    {
        $repository = $this->createMock(BatonnierMandateRepositoryInterface::class);
        $repository->expects(self::once())->method('countMediaUsage')->with(7)->willReturn(1);

        self::assertTrue((new BatonnierMediaUsageChecker($repository))->isUsed(7));
    }

    public function testUnusedPortraitIsReportedAsAvailable(): void
    {
        $repository = $this->createMock(BatonnierMandateRepositoryInterface::class);
        $repository->expects(self::once())->method('countMediaUsage')->with(7)->willReturn(0);

        self::assertFalse((new BatonnierMediaUsageChecker($repository))->isUsed(7));
    }
}
