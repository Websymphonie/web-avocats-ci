<?php

declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Twig;

use InvalidArgumentException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Websymphonie\AdminContext\Domain\Model\Image\ImageModel;
use Websymphonie\AdminContext\Domain\Model\Reglage\ReglageModel;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;

class AppExtension extends AbstractExtension
{
    public function __construct(
        private readonly ContextServiceInterface $service,
    )
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('pluralize', $this->setPluralize(...)),
            new TwigFunction('role', fn(string $constantName) => $this->getRoleValue($constantName)),
            new TwigFunction('imageValue', $this->getImageValue(...)),
            new TwigFunction('reglageValue', $this->getReglageValue(...)),
        ];
    }

    /**
     * @param string $name
     * @return string
     * Utilisation dans twig: role('superadmin')
     */
    private function getRoleValue(string $name): string
    {
        $normalized = strtoupper($name);

        $map = [
            'SUPERADMIN' => UserRolesEnum::SUPER_ADMIN,
            'MANAGER' => UserRolesEnum::ADMIN,
            'USER' => UserRolesEnum::USER,
        ];

        if (!isset($map[$normalized])) {
            throw new InvalidArgumentException("Role '$name' inconnu.");
        }

        return $map[$normalized]->value;
    }

    public function getImageValue(string $name): ImageModel
    {
        return $this->service->getImage($name);
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('ucfirst', $this->getUcfirst(...)),
            new TwigFilter('integer', $this->setInteger(...)),
            new TwigFilter('highlight_search', $this->highlightSearch(...), ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param int $count
     * @param string $singular
     * @param string|null $plural
     * @return string
     */
    public function setPluralize(int $count, string $singular, ?string $plural = null): string
    {
        $plural = $plural ?? $singular . 's';
        $str = $count === 1 ? $singular : $plural;
        return "$count $str";
    }

    public function getUcfirst(string $string): string
    {
        return ucfirst(strtolower($string));
    }

    public function setInteger(string $string): int
    {
        return intval($string);
    }

    public function getReglageValue(string $name): ReglageModel
    {
        return $this->service->getValue($name);
    }

    public function highlightSearch(string $value, string $term): string
    {
        if ($term === '') {
            return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        $parts = preg_split('/(' . preg_quote($term, '/') . ')/iu', $value, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        $highlighted = '';
        foreach ($parts as $index => $part) {
            $escapedPart = htmlspecialchars($part, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $highlighted .= $index % 2 === 1
                ? '<mark class="rounded bg-primary/15 px-0.5 font-inherit text-foreground">' . $escapedPart . '</mark>'
                : $escapedPart;
        }

        return $highlighted;
    }
}
