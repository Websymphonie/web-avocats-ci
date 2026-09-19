<?php

declare(strict_types=1);

namespace Websymphonie\Tests\IdentityContext\Infrastructure\Persistence\Doctrine\Service\User;

use PHPUnit\Framework\TestCase;
use Websymphonie\IdentityContext\Application\Usecase\Command\User\AddUserCommand;
use Websymphonie\IdentityContext\Application\Usecase\Command\User\UpdateProfileCommand;
use Websymphonie\IdentityContext\Application\Usecase\Command\User\UpdateUserCommand;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Service\User\UserService;

final class UserServiceTest extends TestCase
{
    private UserService $service;

    public function testAddUserMapsTheFullAccountIdentity(): void
    {
        $user = $this->service->addUser(new User(), new AddUserCommand(
            name: 'Awa Koné',
            email: 'awa.kone@example.test',
            roles: ['ROLE_ADMIN'],
            enabled: true,
        ));

        self::assertSame('Awa Koné', $user->getName());
        self::assertSame('awa.kone@example.test', $user->getEmail());
        self::assertSame(['ROLE_ADMIN'], $user->getRoles());
        self::assertTrue($user->getEnabled());
    }

    public function testUpdateUserKeepsNameInTheAdministrativeFlow(): void
    {
        $user = $this->service->updateUser(new User(), new UpdateUserCommand(
            id: 42,
            name: 'Mariam Traoré',
            email: 'mariam.traore@example.test',
            roles: ['ROLE_USER'],
            enabled: true,
        ));

        self::assertSame('Mariam Traoré', $user->getName());
        self::assertSame('mariam.traore@example.test', $user->getEmail());
    }

    public function testUserCanBeAssignedAKleBusinessRole(): void
    {
        $user = $this->service->addUser(new User(), new AddUserCommand(
            name: 'Awa Koné',
            email: 'awa.kone@example.test',
            roles: ['ROLE_ADMIN'],
            enabled: true,
        ));

        self::assertSame(['ROLE_ADMIN'], $user->getRoles());
    }

    public function testUpdateProfileMapsNameAndEmail(): void
    {
        $user = (new User())
            ->setName('Ancien nom')
            ->setEmail('old@example.test');

        $updatedUser = $this->service->updateProfile($user, new UpdateProfileCommand(
            id: 42,
            name: 'Nouveau nom',
            email: 'new@example.test',
        ));

        self::assertSame('Nouveau nom', $updatedUser->getName());
        self::assertSame('new@example.test', $updatedUser->getEmail());
    }

    protected function setUp(): void
    {
        $this->service = new UserService();
    }
}
