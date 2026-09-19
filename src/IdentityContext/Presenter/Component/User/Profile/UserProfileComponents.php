<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Component\User\Profile;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;

#[AsTwigComponent('UserProfile', template: 'identity/user/components/profile/user_profile_component.html.twig')]
class UserProfileComponents
{
    public UserRolesEnum $role;

    public function mount(UserRolesEnum $role): void
    {
        $this->role = $role;
    }
}