<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Validator\User;

use Websymphonie\IdentityContext\Application\Usecase\Command\User\UpdateProfileCommand;
use Websymphonie\IdentityContext\Domain\Exception\IdentityAssert;

class UserProfileValidator
{
    public function validate(UpdateProfileCommand $command): void
    {
        IdentityAssert::notEmpty($command->name, message: 'exceptions.users.user_empty_name');
        IdentityAssert::notEmpty($command->email, message: 'exceptions.users.user_empty_email');
        IdentityAssert::email($command->email, message: 'exceptions.users.user_invalid_email');
    }
}