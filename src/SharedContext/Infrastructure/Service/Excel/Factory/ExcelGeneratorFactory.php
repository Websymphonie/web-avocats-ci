<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Infrastructure\Service\Excel\Factory;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv as CSVWriter;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;
use PhpOffice\PhpSpreadsheet\Writer\Ods as ODSWriter;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XLSXWriter;
use Websymphonie\SharedContext\Domain\Enum\ExcelFormat;

class ExcelGeneratorFactory
{
    public function fromExcelFormat(ExcelFormat $format, Spreadsheet $spreadsheet): IWriter
    {
        return match ($format) {
            ExcelFormat::CSV => new CSVWriter($spreadsheet),
            ExcelFormat::ODS => new ODSWriter($spreadsheet),
            ExcelFormat::XLSX => new XLSXWriter($spreadsheet),
        };
    }
}
