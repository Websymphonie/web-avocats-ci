<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Usecase\Command\User;

final readonly class ResendAccountActivationCommand
{
    public function __construct(public int $userId) {}
}
