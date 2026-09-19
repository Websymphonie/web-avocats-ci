<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Infrastructure\Framework\Symfony\Handlers;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Entity\Log\Logs;

class DbLogHandler extends AbstractProcessingHandler
{
    public function __construct(
        private readonly EntityManagerInterface $manager,
        int|string|Level                        $level = Level::Debug, // Défaut
        bool                                    $bubble = true
    )
    {
        parent::__construct($level, $bubble);
    }

    protected function write(LogRecord $record): void
    {
        $log = new Logs();
        $log->setContext($record->context);
        $log->setLevel($record->level->value);
        $log->setLevelName($record->level->name);
        $log->setMessage($record->message);
        $log->setExtra($record->extra);
        $log->setUser($record->extra['user']);
        $this->manager->persist($log);
        $this->manager->flush();
    }
}