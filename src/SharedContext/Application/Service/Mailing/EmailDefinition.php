<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Mailing;

use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\ValueObject\Email;

interface EmailDefinition
{
    public function recipient(): Email;

    public function subject(): string;

    /** @return array<string, mixed> */
    public function subjectVariables(): array;

    public function template(): string;

    /** @return array<string, mixed> */
    public function templateVariables(): array;

    public function locale(): string;

    public function getDomain(): string;
}
