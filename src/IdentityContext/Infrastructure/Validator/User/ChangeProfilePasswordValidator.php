<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Validator\User;

use Websymphonie\IdentityContext\Application\Usecase\Command\Password\ChangeProfilePasswordCommand;
use Websymphonie\IdentityContext\Domain\Exception\IdentityAssert;

class ChangeProfilePasswordValidator
{
    public function validate(ChangeProfilePasswordCommand $command): void
    {
        IdentityAssert::notEmpty($command->currentPassword, message: 'exceptions.users.empty_current_password');
        IdentityAssert::notEmpty($command->password, message: 'exceptions.users.empty_new_password');
        IdentityAssert::notEmpty($command->confirmPassword, message: 'exceptions.users.confirm_new_password');
        IdentityAssert::minLength($command->password, 12, 'exceptions.users.password_too_short');
        IdentityAssert::same($command->password, $command->confirmPassword, 'exceptions.users.user_new_password_not_identically');
    }
}
