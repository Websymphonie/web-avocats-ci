<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Unit\ContentContext\Application\Service;

use PHPUnit\Framework\TestCase;
use Websymphonie\ContentContext\Application\Service\CouncilMemberMediaUsageChecker;
use Websymphonie\ContentContext\Domain\Repository\CouncilMemberRepositoryInterface;

final class CouncilMemberMediaUsageCheckerTest extends TestCase
{
    public function testUsedPortraitIsReported(): void
    {
        $repository = $this->createMock(CouncilMemberRepositoryInterface::class);
        $repository->expects(self::once())->method('countMediaUsage')->with(7)->willReturn(1);

        self::assertTrue((new CouncilMemberMediaUsageChecker($repository))->isUsed(7));
    }

    public function testUnusedPortraitIsAvailable(): void
    {
        $repository = $this->createMock(CouncilMemberRepositoryInterface::class);
        $repository->expects(self::once())->method('countMediaUsage')->with(7)->willReturn(0);

        self::assertFalse((new CouncilMemberMediaUsageChecker($repository))->isUsed(7));
    }
}
