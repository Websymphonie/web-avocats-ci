<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use Websymphonie\SharedContext\Infrastructure\Framework\Symfony\Kernel;

require dirname(__DIR__) . '/vendor/autoload.php';

$kernel = new Kernel('dev', true);
$kernel->boot();

return $kernel->getContainer()->get(EntityManagerInterface::class);