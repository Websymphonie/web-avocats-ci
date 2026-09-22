<?php

declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Mailing;

use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\ValueObject\Email;

interface ReplyToEmailDefinition
{
    public function replyTo(): Email;
}
