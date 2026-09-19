<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Application\Usecase\Command\ResetPassword;

class ResetPasswordCommand
{

    public function __construct(
        public ?string $password = null,
        public ?string $confirmPassword = null,
        public ?string $selector = null,
        public ?string $secret = null,
        public ?string $token = null,
    )
    {
    }
}
