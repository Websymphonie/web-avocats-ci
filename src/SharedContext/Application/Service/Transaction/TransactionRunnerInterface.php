<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Transaction;

interface TransactionRunnerInterface
{
    /** @param callable(): mixed $operation */
    public function run(callable $operation): mixed;
}
