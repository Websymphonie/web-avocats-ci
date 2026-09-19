<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\ViewModel\User;

use Websymphonie\IdentityContext\Domain\Model\User\UserModel;
use Websymphonie\SharedContext\Domain\ViewModel\DetailViewModel;

/** @extends DetailViewModel<UserModel> */
final class UserDetailViewModel extends DetailViewModel
{
    public function __construct(UserModel $userModel)
    {
        parent::__construct($userModel);
    }

    public function getObject(): UserModel
    {
        return $this->object;
    }
}