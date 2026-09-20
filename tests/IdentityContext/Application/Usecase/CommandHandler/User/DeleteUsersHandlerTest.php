<?php

declare(strict_types=1);

namespace Websymphonie\Tests\IdentityContext\Application\Usecase\CommandHandler\User;

use PHPUnit\Framework\TestCase;
use Websymphonie\IdentityContext\Application\Usecase\Command\User\DeleteUsersCommand;
use Websymphonie\IdentityContext\Application\Usecase\CommandHandler\User\DeleteUsersHandler;
use Websymphonie\IdentityContext\Application\Service\User\AdministrativeAccountProtection;
use Websymphonie\IdentityContext\Application\Service\User\CurrentUserProvider;
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;

final class DeleteUsersHandlerTest extends TestCase
{
    public function testItDeletesOnlyUsersFoundByTheRepository(): void
    {
        $first = new User();
        $second = new User();
        $repository = $this->createMock(UserModelRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('findByIds')
            ->with([12, 13, 99])
            ->willReturn([$first, $second]);
        $repository->expects(self::exactly(2))
            ->method('remove')
            ->withConsecutive([$first], [$second]);

        $currentUserProvider = $this->createStub(CurrentUserProvider::class);
        $currentUserProvider->method('getUser')->willReturn(null);
        $accountProtection = new AdministrativeAccountProtection($currentUserProvider, $repository);

        $deletedCount = (new DeleteUsersHandler($repository, $accountProtection))(new DeleteUsersCommand([12, 13, 99]));

        self::assertSame(2, $deletedCount);
    }
}
