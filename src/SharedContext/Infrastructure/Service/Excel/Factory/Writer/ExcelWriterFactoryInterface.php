<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Infrastructure\Service\Excel\Factory\Writer;

use OpenSpout\Writer\WriterInterface;
use Websymphonie\SharedContext\Domain\Enum\ExcelFormat;

interface ExcelWriterFactoryInterface
{
    public function fromFormat(ExcelFormat $format): WriterInterface;
}