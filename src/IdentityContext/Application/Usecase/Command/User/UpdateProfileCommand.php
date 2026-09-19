<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Usecase\Command\User;

final class UpdateProfileCommand
{
    public function __construct(
        public ?int    $id = null,
        public ?string $name = null,
        public ?string $email = null,
    )
    {
    }
}