<?php
declare(strict_types=1);

namespace Websymphonie\Tests\IdentityContext\Application\Usecase\CommandHandler\Activation;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Websymphonie\IdentityContext\Application\Service\Activation\ClockInterface;
use Websymphonie\IdentityContext\Application\Usecase\Command\Activation\ActivateAccountCommand;
use Websymphonie\IdentityContext\Application\Usecase\CommandHandler\Activation\ActivateAccountHandler;
use Websymphonie\IdentityContext\Domain\Exception\Activation\AccountActivationUnavailableException;
use Websymphonie\IdentityContext\Domain\Repository\Activation\AccountActivationRepositoryInterface;
use Websymphonie\IdentityContext\Domain\Service\Password\PasswordHashInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Activation\AccountActivation;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\IdentityContext\Infrastructure\Validator\Activation\ActivateAccountValidator;

final class ActivateAccountHandlerTest extends TestCase
{
    public function testValidTokenDelegatesAtomicActivation(): void
    {
        $now = new DateTimeImmutable('2026-09-11 10:00:00');
        $user = new User();
        $token = 'valid-secret';
        $activation = new AccountActivation($user, 'selector', hash('sha256', $token), $now->modify('+24 hours'), $now);
        $repository = $this->createMock(AccountActivationRepositoryInterface::class);
        $repository->expects(self::once())->method('findBySelector')->with('selector')->willReturn($activation);
        $repository->expects(self::once())->method('activate')->with($activation, $token, 'hashed-password', $now);
        $hash = $this->createMock(PasswordHashInterface::class);
        $hash->expects(self::once())->method('hash')->with($user, 'Valid-password-123')->willReturn('hashed-password');
        $clock = $this->createMock(ClockInterface::class);
        $clock->method('now')->willReturn($now);

        (new ActivateAccountHandler($repository, $hash, new ActivateAccountValidator(), $clock))(new ActivateAccountCommand('selector', $token, 'Valid-password-123', 'Valid-password-123'));
    }

    public function testWrongTokenIsGenericFailure(): void
    {
        $now = new DateTimeImmutable('2026-09-11 10:00:00');
        $activation = new AccountActivation(new User(), 'selector', hash('sha256', 'valid-secret'), $now->modify('+24 hours'), $now);
        $repository = $this->createMock(AccountActivationRepositoryInterface::class);
        $repository->method('findBySelector')->willReturn($activation);
        $clock = $this->createMock(ClockInterface::class);
        $clock->method('now')->willReturn($now);
        $this->expectException(AccountActivationUnavailableException::class);
        (new ActivateAccountHandler($repository, $this->createMock(PasswordHashInterface::class), new ActivateAccountValidator(), $clock))(new ActivateAccountCommand('selector', 'wrong', 'Valid-password-123', 'Valid-password-123'));
    }

    public function testDisabledAccountWithoutActivationDeadlineIsRejected(): void
    {
        $now = new DateTimeImmutable('2026-09-11 10:00:00');
        $user = (new User())->setAccountMustBeVerifedBefore(null);
        $token = 'valid-secret';
        $activation = new AccountActivation($user, 'selector', hash('sha256', $token), $now->modify('+24 hours'), $now);
        $repository = $this->createMock(AccountActivationRepositoryInterface::class);
        $repository->method('findBySelector')->willReturn($activation);
        $clock = $this->createMock(ClockInterface::class);
        $clock->method('now')->willReturn($now);
        $this->expectException(AccountActivationUnavailableException::class);
        (new ActivateAccountHandler($repository, $this->createMock(PasswordHashInterface::class), new ActivateAccountValidator(), $clock))(new ActivateAccountCommand('selector', $token, 'Valid-password-123', 'Valid-password-123'));
    }
}
