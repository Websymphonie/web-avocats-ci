<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Infrastructure\Validator\ResetPassword;

use Websymphonie\AuthContext\Application\Usecase\Command\ResetPassword\ResetPasswordCommand;
use Websymphonie\AuthContext\Domain\Exception\AuthAssert;

class ResetPasswordValidator
{
    public function validate(ResetPasswordCommand $command): void
    {
        AuthAssert::notEmpty($command->password, message: 'exceptions.empty_new_password');
        AuthAssert::notEmpty($command->confirmPassword, message: 'exceptions.empty_confirm_password');
        AuthAssert::minLength($command->password, 12, 'exceptions.password_too_short');
        AuthAssert::same($command->password, $command->confirmPassword, 'exceptions.password_not_match');
    }
}
