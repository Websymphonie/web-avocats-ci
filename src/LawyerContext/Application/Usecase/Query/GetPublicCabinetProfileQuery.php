<?php

declare(strict_types=1);

namespace Websymphonie\LawyerContext\Application\Usecase\Query;

final readonly class GetPublicCabinetProfileQuery
{
    public function __construct(public string $uuid)
    {
    }
}
