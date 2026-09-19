<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Usecase\Command\Password;

final class ChangeUserPasswordCommand
{
    public function __construct(
        public ?int    $id = null,
        public ?string $password = null,
        public ?string $confirmPassword = null,
    )
    {
    }
}