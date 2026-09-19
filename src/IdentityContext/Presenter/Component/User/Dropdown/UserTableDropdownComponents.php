<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Component\User\Dropdown;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\IdentityContext\Domain\Model\User\UserModel;

#[AsTwigComponent('UserTableDropdown', template: 'identity/user/components/dropdown/user_table_dropdown_component.html.twig')]
class UserTableDropdownComponents
{
    public UserModel $user;
    public string $instanceId;

    public function mount(UserModel $user, string $instanceId = ''): void
    {
        $this->user = $user;
        $this->instanceId = $instanceId !== '' ? $instanceId : 'user-' . $user->id;
    }
}
