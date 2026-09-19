<?php

declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Excel;

interface ExcelInterfaces
{
    public function generate(ExcelDefinition $excel): mixed;
}
