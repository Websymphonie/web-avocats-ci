<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Infrastructure\Service\Excel\Exporter;

use SplFileInfo;
use Websymphonie\SharedContext\Domain\Enum\ExcelFormat;

interface ExcelExporterServiceInterface
{
    /**
     * @param list<string> $columnNames
     * @param list<list<string>> $data
     */
    public function export(array $columnNames, array $data, ExcelFormat $format): SplFileInfo;
}
