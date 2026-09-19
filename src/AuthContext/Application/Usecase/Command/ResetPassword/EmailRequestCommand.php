<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Application\Usecase\Command\ResetPassword;


class EmailRequestCommand
{
    public function __construct(public ?string $email = null, public ?string $type = null)
    {
    }
}
