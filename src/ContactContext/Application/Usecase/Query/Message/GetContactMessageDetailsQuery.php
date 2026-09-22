<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Application\Usecase\Query\Message;

final readonly class GetContactMessageDetailsQuery
{
    public function __construct(public string $uuid)
    {
    }
}
