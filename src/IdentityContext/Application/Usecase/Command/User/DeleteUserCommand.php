<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Usecase\Command\User;

final class DeleteUserCommand
{
    public function __construct(public int $id)
    {
    }
}