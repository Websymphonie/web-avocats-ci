<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Model\ValueObject\Secret;

use Override;
use Stringable;
use Websymphonie\IdentityContext\Domain\Exception\IdentityAssert;

final readonly class GeneratedCode implements Stringable
{
    public function __construct(public string $code)
    {
        IdentityAssert::notEmpty($this->code, message: 'exceptions.empty_generated_code');
    }

    #[Override]
    public function __toString(): string
    {
        return $this->code;
    }
}