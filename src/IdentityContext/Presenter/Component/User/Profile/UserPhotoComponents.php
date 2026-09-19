<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Component\User\Profile;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\IdentityContext\Domain\Model\User\UserModel;

#[AsTwigComponent('UserPhoto', template: 'identity/user/components/profile/user_photo_component.html.twig')]
class UserPhotoComponents
{
    public UserModel $user;
    public string $format = "rounded-full";

    public function mount(UserModel $user): void
    {
        $this->user = $user;
    }
}