<?php

declare(strict_types=1);

namespace Websymphonie\Tests\AuthContext\Infrastructure\Persistence\Doctrine\Entity;

use DateTime;
use PHPUnit\Framework\TestCase;
use Websymphonie\AuthContext\Infrastructure\Persistence\Doctrine\Entity\ResetPassword;

final class ResetPasswordTest extends TestCase
{
    public function testTokenUsesTheStoredExpirationDate(): void
    {
        $resetPassword = new ResetPassword();
        $resetPassword->setPasswordResetExpiresAt(new DateTime('+5 minutes'));

        self::assertFalse($resetPassword->isPasswordResetTokenExpired());

        $resetPassword->setPasswordResetExpiresAt(new DateTime('-1 second'));

        self::assertTrue($resetPassword->isPasswordResetTokenExpired());
    }

    public function testTokenWithoutExpirationIsExpired(): void
    {
        self::assertTrue((new ResetPassword())->isPasswordResetTokenExpired());
    }
}
