<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Application\Usecase\Command\AuthLog;

final class DeleteAuthLogCommand
{
    public function __construct(public int $id)
    {

    }
}