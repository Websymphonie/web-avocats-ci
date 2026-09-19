<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Excel;

use Websymphonie\SharedContext\Domain\Enum\ExcelFormat;

interface ExcelDefinition
{
    public function format(): ExcelFormat;

    public function title(): string;

    public function template(): string;

    /** @return array<string, mixed> */
    public function templateVariables(): array;
}
