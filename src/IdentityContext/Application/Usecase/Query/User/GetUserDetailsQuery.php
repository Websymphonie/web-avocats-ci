<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Usecase\Query\User;

final class GetUserDetailsQuery
{
    public function __construct(public int $userId)
    {
    }
}