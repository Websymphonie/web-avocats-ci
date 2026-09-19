<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MenuExtension extends AbstractExtension
{

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_active', [$this, 'isActive']),
            new TwigFunction('is_parent_active', [$this, 'isParentActive']),
        ];
    }

    /**
     * Vérifie si une des routes enfants est active (pour les sous-menus)
     *
     * @param list<string> $childRoutes Liste des routes enfants
     * @return bool
     */
    public function isParentActive(array $childRoutes): bool
    {
        return $this->isActive($childRoutes);
    }

    /**
     * Vérifie si une route est active
     *
     * @param string|list<string> $routes Un nom de route ou un tableau de noms de routes
     * @return bool
     */
    public function isActive(string|array $routes): bool
    {
        $currentRoute = $this->requestStack->getCurrentRequest()?->attributes->get('_route');
        $routes = (array)$routes; // Convertir en tableau si une seule route est fournie
        return in_array($currentRoute, $routes, true);
    }
}
