<?php

declare(strict_types=1);

namespace Websymphonie\AuthContext\Presenter\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Repository\AuthLog\AuthLogRepository;
use Websymphonie\SharedContext\Domain\Service\Flash\FlashServiceInterface;

readonly class AuthenticatorSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface   $securityLogger,
        private AuthLogRepository $authLogRepository,
        private RequestStack      $requestStack,
        private FlashServiceInterface $flash,
    )
    {
    }

    /** @return array<string, string> */
    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
            'Symfony\Component\Security\Http\Event\LogoutEvent' => 'onSecurityLogout',
            'security.logout_on_change' => 'onSecurityLogoutOnChange',
            SecurityEvents::SWITCH_USER => 'onSecuritySwitchUser',
        ];
    }

    /**
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event): void
    {
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        ['user_IP' => $userIP] = $this->getRouteNameAndUserIp();
        $securityToken = $event->getAuthenticationToken();
        $userEmail = $this->getUserEmail($securityToken);
        $request = $this->requestStack->getCurrentRequest();
        if ($request && $request->cookies->get('REMEMBERME')) {
            $this->securityLogger->info("Un utilisateur annonyme ayant une adresse IP
            '$userIP' a évolué en entité user avec l'email '$userEmail' grâce à une REMEMBERME cookie :-)");
            $this->authLogRepository->addSuccessFulAuthAttempt($userEmail, $userIP, true);
        } else {
            $this->securityLogger->info("Un utilisateur annonyme ayant une adresse IP
            '$userIP' a évolué en entité user avec l'email '$userEmail' :-)");
            $this->authLogRepository->addSuccessFulAuthAttempt($userEmail, $userIP);
        }
    }

    /** @return array{route_name: string, user_IP: string} */
    private function getRouteNameAndUserIp(): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return [
                'route_name' => 'Inconnue',
                'user_IP' => 'Inconnue'
            ];
        }
        return [
            'route_name' => (string) ($request->attributes->get('_route') ?? 'Inconnue'),
            'user_IP' => $request->getClientIp() ?? 'Inconnue'
        ];
    }

    /**
     * @param TokenInterface $securityToken
     * @return string
     */
    private function getUserEmail(TokenInterface $securityToken): string
    {
        /**
         * @var User $user
         */
        $user = $securityToken->getUser();
        return $user->getEmail();
    }

    /**
     * @param LogoutEvent $event
     */
    public function onSecurityLogout(LogoutEvent $event): void
    {
        /**
         * @var RedirectResponse|null $response
         */
        $response = $event->getResponse();
        /**
         * @var TokenInterface|null $securityToken
         */
        $securityToken = $event->getToken();

        if (!$response || !$securityToken) {
            return;
        }
        ['user_IP' => $userIP] = $this->getRouteNameAndUserIp();
        $userEmail = $this->getUserEmail($securityToken);
        $targetUrl = $response->getTargetUrl();
        $this->securityLogger->info("Un utilisateur annonyme ayant une adresse IP
            '$userIP' et l'email '$userEmail'
             s'est déconnecté et a été redirigé vers l'url suivante: $targetUrl :-)");

        $this->authLogRepository->addSuccessFulLogouthAttempt($userEmail, $userIP);
        $this->flash->success('Déconnexion réussie.');
    }

    public function onSecurityLogoutOnChange(): void
    {
    }

    public function onSecuritySwitchUser(SwitchUserEvent $event): void
    {
    }
}
