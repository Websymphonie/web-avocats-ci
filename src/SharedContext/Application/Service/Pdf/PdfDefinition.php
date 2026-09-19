<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Pdf;

use Websymphonie\SharedContext\Domain\Enum\PdfFormat;

interface PdfDefinition
{
    public function format(): PdfFormat;

    public function template(): string;

    /** @return array<string, mixed> */
    public function templateVariables(): array;
}
