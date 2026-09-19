<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Validator\Activation;

use Websymphonie\IdentityContext\Application\Usecase\Command\Activation\ActivateAccountCommand;
use Websymphonie\IdentityContext\Domain\Exception\IdentityAssert;

final class ActivateAccountValidator
{
    public function validate(ActivateAccountCommand $command): void
    {
        IdentityAssert::notEmpty($command->password, 'exceptions.users.user_empty_password');
        IdentityAssert::notEmpty($command->confirmPassword, 'exceptions.users.user_confirm_password');
        IdentityAssert::minLength($command->password, 12, 'exceptions.users.password_too_short');
        IdentityAssert::same($command->password, $command->confirmPassword, 'exceptions.users.user_password_not_identically');
    }
}
