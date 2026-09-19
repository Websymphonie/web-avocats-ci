<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Infrastructure\Service\Excel\Importer;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;
use OpenSpout\Reader\SheetInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Websymphonie\SharedContext\Domain\Enum\ExcelFormat;
use Websymphonie\SharedContext\Infrastructure\Service\Excel\Factory\Reader\ExcelReaderFactory;

readonly class ExcelImportService implements ExcelImportServiceInterface
{
    public function __construct(
        private ExcelReaderFactory $factory
    )
    {
    }

    /**
     * @return list<list<mixed>>
     * @throws IOException
     * @throws ReaderNotOpenedException
     */
    public function import(UploadedFile $file): array
    {
        $data = [];
        $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        $format = ExcelFormat::fromExtension($extension);
        $reader = $this->factory->fromFormat($format);

        $reader->open($file->getPathname());

        /** @var SheetInterface<\OpenSpout\Reader\RowIteratorInterface> $sheet */
        foreach ($reader->getSheetIterator() as $sheet) {
            /** @var Row $row */
            foreach ($sheet->getRowIterator() as $row) {
                $data[] = $row->toArray();
            }
        }

        return $data;
    }
}
