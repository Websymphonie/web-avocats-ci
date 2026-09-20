<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Infrastructure\EventListener;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Websymphonie\IdentityContext\Domain\Enum\PermissionEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Infrastructure\Security\Voters\RoleGroupAccessVoter;

#[AsEventListener(event: 'kernel.controller', method: 'onKernelController')]
final readonly class HasGroupAccessListener
{
    public function __construct(
        private AuthorizationCheckerInterface $auth
    )
    {
    }

    /**
     * @throws ReflectionException
     */
    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();
        $classReflection = null;

        // ✅ Cas 1 : contrôleur classique [objet, méthode]
        if (is_array($controller)) {
            $reflection = new ReflectionMethod($controller[0], $controller[1]);
            $classReflection = new ReflectionClass($controller[0]);
        } // ✅ Cas 2 : contrôleur défini comme closure ou nom de fonction
        elseif ($controller instanceof Closure || is_string($controller)) {
            $reflection = new ReflectionFunction($controller);
            $classReflection = null;
        } // ✅ Cas 3 : contrôleur invocable (ex: __invoke())
        elseif (is_object($controller)) {
            $class = new ReflectionClass($controller);

            // Si la classe a une méthode __invoke(), on la vérifie aussi.
            if ($class->hasMethod('__invoke')) {
                $reflection = $class->getMethod('__invoke');
                $classReflection = $class;
            } else {
                return; // rien à faire
            }
        } // 🚫 Cas non pris en charge
        else {
            return;
        }

        // La méthode est prioritaire. Si elle ne porte pas l'attribut,
        // on applique celui de la classe. Cela évite de combiner
        // implicitement deux groupes différents.
        $attributes = $reflection->getAttributes(HasGroupAccess::class);
        if ($attributes === [] && $classReflection !== null) {
            $attributes = $classReflection->getAttributes(HasGroupAccess::class);
        }

        foreach ($attributes as $attribute) {
            /** @var HasGroupAccess $instance */
            $instance = $attribute->newInstance();
            $this->checkAccess($instance);
        }
    }

    private function checkAccess(HasGroupAccess $attribute): void
    {
        // 🚨 utilisateur non authentifié → on laisse Symfony gérer
        if (!$this->auth->isGranted('IS_AUTHENTICATED_FULLY')) {
            return;
            //throw new AuthenticationCredentialsNotFoundException();
        }

        $group = $attribute->group->value;

        $granted = $attribute->group instanceof PermissionEnum
            ? $this->auth->isGranted($group)
            : $this->auth->isGranted(RoleGroupAccessVoter::ROLE_GROUP_ACCESS, $group);

        if (!$granted) {
            throw new AccessDeniedException($group);
        }
    }

}
