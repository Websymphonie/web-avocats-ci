<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Infrastructure\Validator\ResetPassword;

use Websymphonie\AuthContext\Application\Usecase\Command\ResetPassword\EmailRequestCommand;
use Websymphonie\AuthContext\Domain\Exception\AuthAssert;

class EmailValidator
{
    public function validate(EmailRequestCommand $command): void
    {
        AuthAssert::notEmpty($command->email, message: 'exceptions.empty_email');
        AuthAssert::email($command->email, message: 'exceptions.invalid_email');
    }
}