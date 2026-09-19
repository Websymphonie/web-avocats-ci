<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Usecase\Command\Activation;

final class ActivateAccountCommand
{
    public function __construct(
        public string $selector,
        public string $token,
        public ?string $password = null,
        public ?string $confirmPassword = null,
    ) {}
}
