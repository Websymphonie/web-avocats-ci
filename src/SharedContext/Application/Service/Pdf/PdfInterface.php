<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Pdf;

interface PdfInterface
{
    public function generate(PdfDefinition $pdf): mixed;
}