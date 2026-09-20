<?php

declare(strict_types=1);

namespace Websymphonie\Tests\IdentityContext\Application\Usecase\CommandHandler\User;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Websymphonie\IdentityContext\Application\Service\Activation\ClockInterface;
use Websymphonie\IdentityContext\Application\Service\User\AdministrativeAccountProtection;
use Websymphonie\IdentityContext\Application\Service\User\CurrentUserProvider;
use Websymphonie\IdentityContext\Application\Usecase\Command\User\UpdateUserCommand;
use Websymphonie\IdentityContext\Application\Usecase\CommandHandler\User\UpdateUserHandler;
use Websymphonie\IdentityContext\Domain\Event\UserRoleAssignedEvent;
use Websymphonie\IdentityContext\Domain\Event\UserRoleRemovedEvent;
use Websymphonie\IdentityContext\Domain\Repository\Activation\AccountActivationRepositoryInterface;
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\IdentityContext\Domain\Service\User\UserServiceInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\IdentityContext\Infrastructure\Security\RememberMeTokenRevoker;
use Websymphonie\IdentityContext\Infrastructure\Validator\User\UpdateUserValidator;
use Websymphonie\SharedContext\Application\Service\Actor\CurrentActorProvider;
use Websymphonie\SharedContext\Domain\Service\EventDispatcher\EventDispatcher;

final class UpdateUserHandlerTest extends TestCase
{
    public function testRoleChangesEmitOneAuditEventPerAddedOrRemovedRole(): void
    {
        $user = $this->user(['ROLE_USER']);
        $repository = $this->createMock(UserModelRepositoryInterface::class);
        $repository->expects(self::once())->method('getById')->willReturn($user);
        $repository->expects(self::once())->method('update')->willReturn($user);

        $service = $this->createMock(UserServiceInterface::class);
        $service->expects(self::once())->method('updateUser')->willReturnCallback(
            static function (User $target, UpdateUserCommand $command): User {
                $target->setRoles($command->roles ?? []);

                return $target;
            },
        );

        $dispatcher = $this->createMock(EventDispatcher::class);
        $dispatcher->expects(self::once())->method('dispatch')->with(self::callback(
            static function (array $events): bool {
                self::assertCount(2, $events);
                self::assertInstanceOf(UserRoleAssignedEvent::class, $events[0]);
                self::assertInstanceOf(UserRoleRemovedEvent::class, $events[1]);

                return true;
            },
        ));

        $handler = $this->handler($repository, $service, $dispatcher, $user);
        $handler(new UpdateUserCommand(
            id: 1,
            name: 'Utilisateur',
            email: 'user@example.test',
            roles: ['ROLE_ADMIN'],
            enabled: true,
        ));
    }

    public function testUnchangedRolesDoNotEmitAuditEvents(): void
    {
        $user = $this->user(['ROLE_USER']);
        $repository = $this->createMock(UserModelRepositoryInterface::class);
        $repository->expects(self::once())->method('getById')->willReturn($user);
        $repository->expects(self::once())->method('update')->willReturn($user);

        $service = $this->createMock(UserServiceInterface::class);
        $service->method('updateUser')->willReturn($user);

        $dispatcher = $this->createMock(EventDispatcher::class);
        $dispatcher->expects(self::once())->method('dispatch')->with([]);

        $handler = $this->handler($repository, $service, $dispatcher, $user);
        $handler(new UpdateUserCommand(
            id: 1,
            name: 'Utilisateur',
            email: 'user@example.test',
            roles: ['ROLE_USER'],
            enabled: true,
        ));
    }

    /** @param list<string> $roles */
    private function user(array $roles): User
    {
        $user = (new User())
            ->setName('Utilisateur')
            ->setEmail('user@example.test')
            ->setRoles($roles);
        $user->setEnabled(true);

        return $user;
    }

    private function handler(
        UserModelRepositoryInterface $repository,
        UserServiceInterface $service,
        EventDispatcher $dispatcher,
        User $actor,
    ): UpdateUserHandler {
        $currentUserProvider = $this->createStub(CurrentUserProvider::class);
        $currentUserProvider->method('getUser')->willReturn($actor);
        $currentActorProvider = $this->createStub(CurrentActorProvider::class);
        $currentActorProvider->method('currentUserId')->willReturn(99);

        $accountProtection = new AdministrativeAccountProtection($currentUserProvider, $repository);
        $connection = $this->createMock(Connection::class);
        $connection->method('executeStatement')->willReturn(0);

        $clock = $this->createStub(ClockInterface::class);
        $clock->method('now')->willReturn(new DateTimeImmutable());

        return new UpdateUserHandler(
            $repository,
            new UpdateUserValidator(),
            $service,
            $this->createMock(AccountActivationRepositoryInterface::class),
            $clock,
            new RememberMeTokenRevoker($connection),
            $accountProtection,
            $dispatcher,
            $currentActorProvider,
        );
    }
}
