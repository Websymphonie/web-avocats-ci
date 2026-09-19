<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Security\Voter\Permission;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Websymphonie\IdentityContext\Domain\Enum\PermissionEnum;
use Websymphonie\IdentityContext\Domain\Service\User\PermissionsInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;

/** @extends Voter<string, mixed> */
final class PermissionVoter extends Voter
{
    public function __construct(
        private readonly PermissionsInterface $permissionsService
    )
    {
    }

    protected function supports(string $attribute, $subject): bool
    {
        return PermissionEnum::tryFrom($attribute) !== null;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        $permission = PermissionEnum::from($attribute);

        return $this->permissionsService->has($user, $permission);
    }
}
