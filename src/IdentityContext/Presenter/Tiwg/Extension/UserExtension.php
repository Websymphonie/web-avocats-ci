<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Tiwg\Extension;

use Exception;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;
use Websymphonie\IdentityContext\Domain\Model\User\UserModel;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Service\User\UserRoleResolver;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Factory\UserFactory;

final class UserExtension extends AbstractExtension
{
    public function __construct(
        private readonly UserFactory      $factory,
        private readonly UserRoleResolver $roleResolver,
    )
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('user_role_badge', [$this, 'renderUserRoleBadge'], ['is_safe' => ['html']]),
            new TwigFunction('userToModel', [$this, 'renderUserToModel']),
        ];
    }

    /**
     * @param array<array-key, string>|User|UserModel|UserRolesEnum|string $input
     * @throws Exception
     */
    public function renderUserRoleBadge(
        User|UserModel|UserRolesEnum|string|array $input
    ): string
    {
        $roles = match (true) {
            $input instanceof User => $input->getRoles(),
            $input instanceof UserModel => $input->roles,
            $input instanceof UserRolesEnum => [$input->value],
            is_string($input) => [$input],
            default => $input,
        };

        $role = $this->roleResolver->resolveMain($roles);

        if (!$role) {
            return '';
        }

        return sprintf(
            '<span class="%s">%s</span>',
            $role->badge()->badgeVariant(),
            $role->label()
        );
    }


    public function renderUserToModel(User $user): UserModel
    {
        return $this->factory->fromEntity($user);
    }
}
