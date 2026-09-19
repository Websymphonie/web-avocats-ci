<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Application\Usecase\Command\Log;

final class DeleteLogCommand
{
    public function __construct(public int $id)
    {

    }
}