<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Usecase\Query\User;

final class GetUserListQuery
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $role = null,
        public ?bool   $enabled = null,
        public int     $page = 1,
        public int     $limit = 15,
    )
    {
    }
}
