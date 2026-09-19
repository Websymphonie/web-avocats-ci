<?php

declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Csv;

enum CsvCellType: string
{
    case TEXT = 'text';
    case NUMBER = 'number';
    case DATE = 'date';
}
