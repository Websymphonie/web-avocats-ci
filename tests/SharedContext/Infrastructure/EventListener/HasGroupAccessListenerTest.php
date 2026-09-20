<?php

declare(strict_types=1);

namespace Websymphonie\Tests\SharedContext\Infrastructure\EventListener;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Infrastructure\EventListener\HasGroupAccessListener;
use Websymphonie\SharedContext\Infrastructure\Security\Voters\RoleGroupAccessVoter;

final class HasGroupAccessListenerTest extends TestCase
{
    public function testClassAttributeIsAppliedToArrayController(): void
    {
        $checked = [];
        $listener = new HasGroupAccessListener($this->authorizationChecker($checked, 'SUPER'));

        $listener->onKernelController($this->controllerEvent([new ClassGuardedController(), 'edit']));

        self::assertSame('SUPER', $checked[1][1]);
    }

    public function testMethodAttributeTakesPrecedenceOverClassAttribute(): void
    {
        $checked = [];
        $listener = new HasGroupAccessListener($this->authorizationChecker($checked, 'ADMIN'));

        $listener->onKernelController($this->controllerEvent([new MethodGuardedController(), 'edit']));

        self::assertSame(['IS_AUTHENTICATED_FULLY', RoleGroupAccessVoter::ROLE_GROUP_ACCESS], array_column($checked, 0));
        self::assertSame('ADMIN', $checked[1][1]);
    }

    public function testPermissionWithoutTheRequiredGroupIsDenied(): void
    {
        $checked = [];
        $listener = new HasGroupAccessListener($this->authorizationChecker($checked, null));

        $this->expectException(AccessDeniedException::class);
        $listener->onKernelController($this->controllerEvent([new ClassGuardedController(), 'edit']));
    }

    /** @param list<array{string, mixed}> $checked */
    private function authorizationChecker(array &$checked, ?string $grantedGroup): AuthorizationCheckerInterface
    {
        $checker = $this->createMock(AuthorizationCheckerInterface::class);
        $checker->method('isGranted')->willReturnCallback(
            static function (string $attribute, mixed $subject = null) use (&$checked, $grantedGroup): bool {
                $checked[] = [$attribute, $subject];

                return $attribute === 'IS_AUTHENTICATED_FULLY' || $subject === $grantedGroup;
            },
        );

        return $checker;
    }

    private function controllerEvent(callable $controller): ControllerEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);

        return new ControllerEvent($kernel, $controller, new Request(), HttpKernelInterface::MAIN_REQUEST);
    }
}

#[HasGroupAccess(RoleGroupEnum::SUPER)]
final class ClassGuardedController
{
    public function edit(): void
    {
    }
}

#[HasGroupAccess(RoleGroupEnum::SUPER)]
final class MethodGuardedController
{
    #[HasGroupAccess(RoleGroupEnum::ADMIN)]
    public function edit(): void
    {
    }
}
