<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Transaction;

use Doctrine\ORM\EntityManagerInterface;
use Websymphonie\SharedContext\Application\Service\Transaction\TransactionRunnerInterface;

final readonly class DoctrineTransactionRunner implements TransactionRunnerInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function run(callable $operation): mixed
    {
        return $this->entityManager->wrapInTransaction($operation);
    }
}
