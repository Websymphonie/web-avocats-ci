<?php

declare(strict_types=1);

namespace Websymphonie\AdminContext\Presenter\EventSubscriber\Maintenance;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;

readonly class MaintenanceSubscriber implements EventSubscriberInterface
{
    /** @var list<string> */
    private const array ACCESSIBLE_ROUTES = [
        'app_login',
        'app_logout',
    ];

    public function __construct(
        private Environment                   $twig,
        private string                        $maintenanceON,
        private AuthorizationCheckerInterface $authorizationChecker,
    )
    {
    }

    /**
     * @return array<class-string, array{0: string, 1: int}>
     */
    public static function getSubscribedEvents(): array
    {
        // Le contrôle doit intervenir après le routeur et le pare-feu de sécurité.
        return [RequestEvent::class => ['onKernelRequest', -10]];
    }

    /**
     * @param RequestEvent $event
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest() || !file_exists($this->maintenanceON)) {
            return;
        }

        $route = $event->getRequest()->attributes->getString('_route');
        if (in_array($route, self::ACCESSIBLE_ROUTES, true)) {
            return;
        }

        if ($this->authorizationChecker->isGranted(UserRolesEnum::SUPER_ADMIN->value)) {
            return;
        }

        $html = $this->twig->render('bundles/TwigBundle/Exception/maintenance.html.twig', [
            'title' => 'En maintenance',
            'isAuthenticated' => $this->authorizationChecker->isGranted('IS_AUTHENTICATED'),
        ]);

        $event->setResponse(new Response($html, Response::HTTP_SERVICE_UNAVAILABLE, [
            'Cache-Control' => 'no-store, private',
            'Retry-After' => '300',
        ]));
        $event->stopPropagation();
    }

}
