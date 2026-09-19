<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LearningContext\Domain\Model;

use PHPUnit\Framework\TestCase;
use Websymphonie\LearningContext\Domain\Enum\EnrollmentSource;
use Websymphonie\LearningContext\Domain\Enum\EnrollmentStatus;
use Websymphonie\LearningContext\Domain\Model\Enrollment;

final class EnrollmentTest extends TestCase
{
    public function testNewEnrollmentIsActiveAndCanBeRevokedThenReactivated(): void
    {
        $enrollment = new Enrollment(1, 'uuid', 10, 20);
        self::assertTrue($enrollment->isActive());
        $enrollment->revoke();
        self::assertSame(EnrollmentStatus::REVOKED, $enrollment->status);
        self::assertNotNull($enrollment->revokedAt);
        $enrollment->activate(EnrollmentSource::ADMIN_GRANT);
        self::assertSame(EnrollmentStatus::ACTIVE, $enrollment->status);
        self::assertSame(EnrollmentSource::ADMIN_GRANT, $enrollment->source);
        self::assertNull($enrollment->revokedAt);
    }
}
