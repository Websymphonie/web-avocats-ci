<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Enum;

enum CurrencyEnum: string
{
    case LOCAL = 'fr';
    case DEVISE = 'XOF';
    case DEVISE_SYMBOL = 'CFA';
}
