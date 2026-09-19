<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Infrastructure\Service\Excel\Importer;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface ExcelImportServiceInterface
{
    /** @return list<list<mixed>> */
    public function import(UploadedFile $file): array;
}
