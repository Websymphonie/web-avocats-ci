<?php

declare(strict_types=1);

namespace Websymphonie\Tests\AuthContext\Application\Usecase\CommandHandler\ResetPassword;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Websymphonie\AuthContext\Application\Usecase\Command\ResetPassword\EmailRequestCommand;
use Websymphonie\AuthContext\Application\Usecase\CommandHandler\ResetPassword\EmailRequestHandler;
use Websymphonie\AuthContext\Domain\Enum\ResetTypeEnum;
use Websymphonie\AuthContext\Infrastructure\Persistence\Doctrine\Entity\ResetPassword;
use Websymphonie\AuthContext\Infrastructure\Persistence\Doctrine\Repository\ResetPasswordRepository;
use Websymphonie\AuthContext\Infrastructure\Validator\ResetPassword\EmailValidator;
use Websymphonie\IdentityContext\Domain\Model\ValueObject\Secret\GeneratedToken;
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\IdentityContext\Domain\Service\Password\SecretGeneratorInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\SharedContext\Domain\Service\EventDispatcher\EventDispatcher;

final class EmailRequestHandlerTest extends TestCase
{
    public function testFirstRequestSynchronizesTheResetPasswordRelationBeforeDispatchingTheEvent(): void
    {
        $user = (new User())
            ->setName('Awa Koné')
            ->setEmail('awa@example.test');
        (new ReflectionProperty($user, 'id'))->setValue($user, 42);

        $userRepository = $this->createMock(UserModelRepositoryInterface::class);
        $userRepository->expects(self::once())
            ->method('getByEmail')
            ->with('awa@example.test')
            ->willReturn($user);

        $resetPasswordRepository = $this->createMock(ResetPasswordRepository::class);
        $resetPasswordRepository->expects(self::once())
            ->method('create')
            ->with(self::callback(static fn (ResetPassword $resetPassword): bool => $resetPassword->getUser() === $user))
            ->willReturnCallback(static fn (ResetPassword $resetPassword): ResetPassword => $resetPassword);

        $generator = $this->createMock(SecretGeneratorInterface::class);
        $generator->expects(self::once())
            ->method('generateToken')
            ->willReturn(new GeneratedToken('secure-reset-token'));

        $dispatcher = $this->createMock(EventDispatcher::class);
        $dispatcher->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(static fn (array $events): bool => count($events) === 1));

        $handler = new EmailRequestHandler(
            $userRepository,
            $resetPasswordRepository,
            new EmailValidator(),
            $dispatcher,
            $generator,
        );

        $result = $handler(new EmailRequestCommand('awa@example.test', ResetTypeEnum::TOKEN->value));

        self::assertSame($user, $result);
        self::assertInstanceOf(ResetPassword::class, $user->getResetPassword());
        self::assertSame($user, $user->getResetPassword()->getUser());
        self::assertSame(ResetTypeEnum::TOKEN->value, $user->getResetPassword()->getResetType());
    }
}
