<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Infrastructure\Service\Excel\Exporter;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Exception\InvalidArgumentException;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Writer\Exception\WriterNotOpenedException;
use SplFileInfo;
use Websymphonie\SharedContext\Domain\Enum\ExcelFormat;
use Websymphonie\SharedContext\Infrastructure\Service\Excel\Factory\Writer\ExcelWriterFactory;

readonly class ExcelExporterService implements ExcelExporterServiceInterface
{
    public function __construct(
        private ExcelWriterFactory $factory
    )
    {
    }

    /**
     * @param list<string> $columnNames
     * @param list<list<string>> $data
     * @throws IOException
     * @throws WriterNotOpenedException
     * @throws InvalidArgumentException
     */
    public function export(array $columnNames, array $data, ExcelFormat $format): SplFileInfo
    {
        $filePath = "export.{$format->extension()}";

        $rows = [Row::fromValues($columnNames, (new Style())->setFontBold())];

        foreach ($data as $row) {
            $values = array_map('strip_tags', $row);
            $rows[] = Row::fromValues($values);
        }

        $writer = $this->factory->fromFormat($format);

        $writer->openToBrowser($filePath);
        $writer->addRows($rows);

        $writer->close();

        return new SplFileInfo($filePath);
    }
}
