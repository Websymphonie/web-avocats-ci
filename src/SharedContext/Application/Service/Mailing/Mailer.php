<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Mailing;

interface Mailer
{
    public function send(EmailDefinition $email): void;
}
