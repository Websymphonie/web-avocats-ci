<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Presenter\Security;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Websymphonie\IdentityContext\Domain\Service\User\UserRolesInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\IdentityContext\Presenter\Constant\UserRouteConstants;
use Websymphonie\SharedContext\Domain\Service\Flash\FlashServiceInterface;
use Websymphonie\SharedContext\Presenter\Service\SafeRedirectUrlResolver;

class AppAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly LoggerInterface       $logger,
        private readonly RouterInterface       $router,
        private readonly BruteForceChecker     $bruteForceChecker,
        private readonly UserRolesInterface    $roleResolver,
        private readonly FlashServiceInterface $flash,
    )
    {
    }

    public function authenticate(Request $request): Passport
    {
        $password = $request->getPayload()->getString('password');
        $email = $request->getPayload()->getString('email');
        $csrfToken = $request->getPayload()->getString('csrf_token');

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($password),
            [new CsrfTokenBadge('login', $csrfToken), new RememberMeBadge(),]
        );
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        try {
            $this->bruteForceChecker->addFailedAttempt(
                emailEntered: $request->getPayload()->getString('email'),
                userIP: $request->getClientIp()
            );
        } catch (Exception $e) {
            $this->logger->error('Unable to record failed authentication attempt.', [
                'exception' => $e,
            ]);
        }
        $this->flash->danger('Identifiants incorrects. Veuillez réessayer.');

        return new RedirectResponse($this->router->generate(UserRouteConstants::LOGIN_ROUTE));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse(SafeRedirectUrlResolver::resolve(
                request: $request,
                fallback: $this->router->generate(UserRouteConstants::HOME_ROUTE),
                candidate: $targetPath
            ));
        }

        /** @var User $user */
        $user = $token->getUser();

        $role = $this->roleResolver->resolveMain($user->getRoles());
        if ($role !== null) {
            return new RedirectResponse($this->router->generate($role->route()));
        }

        return new RedirectResponse($this->router->generate(UserRouteConstants::HOME_ROUTE));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(UserRouteConstants::LOGIN_ROUTE);
    }
}
