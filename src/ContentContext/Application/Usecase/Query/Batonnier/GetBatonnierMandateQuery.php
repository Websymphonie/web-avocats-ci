<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Query\Batonnier;

final readonly class GetBatonnierMandateQuery
{
    public function __construct(public int $id)
    {
    }
}
