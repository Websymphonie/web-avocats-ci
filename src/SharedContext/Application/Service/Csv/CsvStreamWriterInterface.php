<?php

declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Csv;

use Symfony\Component\HttpFoundation\StreamedResponse;

interface CsvStreamWriterInterface
{
    public function response(CsvDownloadDefinition $definition): StreamedResponse;
}
