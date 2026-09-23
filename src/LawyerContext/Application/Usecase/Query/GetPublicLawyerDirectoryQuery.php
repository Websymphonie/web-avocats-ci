<?php

declare(strict_types=1);

namespace Websymphonie\LawyerContext\Application\Usecase\Query;

final readonly class GetPublicLawyerDirectoryQuery
{
    public function __construct(
        public string $name = '',
        public string $cabinet = '',
        public string $location = '',
        public int $page = 1,
        public int $limit = 12,
    ) {
    }
}
