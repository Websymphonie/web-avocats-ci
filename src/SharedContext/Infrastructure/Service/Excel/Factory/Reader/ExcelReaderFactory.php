<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Infrastructure\Service\Excel\Factory\Reader;

use OpenSpout\Reader\CSV\Reader as CSVReader;
use OpenSpout\Reader\ReaderInterface;
use OpenSpout\Reader\ODS\Reader as ODSReader;
use OpenSpout\Reader\XLSX\Reader as XLSXReader;
use Websymphonie\SharedContext\Domain\Enum\ExcelFormat;

class ExcelReaderFactory implements ExcelReaderFactoryInterface
{
    /** @return CSVReader|ODSReader|XLSXReader */
    public function fromFormat(ExcelFormat $format): ReaderInterface
    {
        return match ($format) {
            ExcelFormat::CSV => new CSVReader(),
            ExcelFormat::ODS => new ODSReader(),
            ExcelFormat::XLSX => new XLSXReader(),
        };
    }
}
