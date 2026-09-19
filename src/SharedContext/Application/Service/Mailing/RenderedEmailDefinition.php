<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Mailing;

interface RenderedEmailDefinition extends EmailDefinition
{
    public function htmlBody(): string;
    public function textBody(): string;
}
