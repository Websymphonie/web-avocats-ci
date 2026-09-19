<?php

declare(strict_types=1);

namespace Websymphonie\Tests\AdminContext\Presenter\EventSubscriber\Maintenance;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;
use Websymphonie\AdminContext\Presenter\EventSubscriber\Maintenance\MaintenanceSubscriber;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;

final class MaintenanceSubscriberTest extends TestCase
{
    private string $maintenanceFile;

    protected function setUp(): void
    {
        $this->maintenanceFile = sys_get_temp_dir().'/ws-immobilier-maintenance-'.bin2hex(random_bytes(8));
    }

    protected function tearDown(): void
    {
        if (file_exists($this->maintenanceFile)) {
            unlink($this->maintenanceFile);
        }
    }

    public function testApplicationRemainsAccessibleWhenMaintenanceIsDisabled(): void
    {
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->expects(self::never())->method('isGranted');

        $event = $this->createEvent('app_admin');
        $this->createSubscriber($authorizationChecker)->onKernelRequest($event);

        self::assertNull($event->getResponse());
    }

    public function testSuperAdministratorKeepsAccessDuringMaintenance(): void
    {
        touch($this->maintenanceFile);
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with(UserRolesEnum::SUPER_ADMIN->value)
            ->willReturn(true);

        $event = $this->createEvent('app_admin');
        $this->createSubscriber($authorizationChecker)->onKernelRequest($event);

        self::assertNull($event->getResponse());
    }

    public function testOtherUsersReceiveAServiceUnavailableResponseDuringMaintenance(): void
    {
        touch($this->maintenanceFile);
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->expects(self::exactly(2))
            ->method('isGranted')
            ->willReturnMap([
                [UserRolesEnum::SUPER_ADMIN->value, null, false],
                ['IS_AUTHENTICATED', null, true],
            ]);

        $event = $this->createEvent('app_admin');
        $this->createSubscriber($authorizationChecker)->onKernelRequest($event);

        $response = $event->getResponse();
        self::assertNotNull($response);
        self::assertSame(503, $response->getStatusCode());
        self::assertSame('300', $response->headers->get('Retry-After'));
        self::assertStringContainsString('maintenance', $response->getContent());
    }

    public function testLoginRemainsAccessibleDuringMaintenance(): void
    {
        touch($this->maintenanceFile);
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->expects(self::never())->method('isGranted');

        $event = $this->createEvent('app_login');
        $this->createSubscriber($authorizationChecker)->onKernelRequest($event);

        self::assertNull($event->getResponse());
    }

    private function createSubscriber(AuthorizationCheckerInterface&MockObject $authorizationChecker): MaintenanceSubscriber
    {
        $twig = $this->createMock(Environment::class);
        $twig->method('render')->willReturn('<html>maintenance</html>');

        return new MaintenanceSubscriber($twig, $this->maintenanceFile, $authorizationChecker);
    }

    private function createEvent(string $route): RequestEvent
    {
        $request = new Request();
        $request->attributes->set('_route', $route);

        return new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        );
    }
}
