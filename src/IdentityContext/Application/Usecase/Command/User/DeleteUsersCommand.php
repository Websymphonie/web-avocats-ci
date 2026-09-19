<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Usecase\Command\User;

final readonly class DeleteUsersCommand
{
    /** @param list<int> $ids */
    public function __construct(public array $ids)
    {
    }
}
