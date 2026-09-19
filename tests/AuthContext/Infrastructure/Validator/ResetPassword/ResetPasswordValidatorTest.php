<?php

declare(strict_types=1);

namespace Websymphonie\Tests\AuthContext\Infrastructure\Validator\ResetPassword;

use PHPUnit\Framework\TestCase;
use Websymphonie\AuthContext\Application\Usecase\Command\ResetPassword\ResetPasswordCommand;
use Websymphonie\AuthContext\Infrastructure\Validator\ResetPassword\ResetPasswordValidator;
use Websymphonie\SharedContext\Domain\Exception\InvalidArgument;

final class ResetPasswordValidatorTest extends TestCase
{
    public function testAcceptsAConfirmedPasswordWithAtLeastTwelveCharacters(): void
    {
        (new ResetPasswordValidator())->validate(new ResetPasswordCommand(
            password: 'phrase-secrete-2026',
            confirmPassword: 'phrase-secrete-2026',
            token: 'token',
        ));

        self::addToAssertionCount(1);
    }

    public function testRejectsAShortPassword(): void
    {
        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('exceptions.password_too_short');

        (new ResetPasswordValidator())->validate(new ResetPasswordCommand(
            password: 'trop-court',
            confirmPassword: 'trop-court',
            token: 'token',
        ));
    }
}
