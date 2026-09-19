<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Model\ValueObject;

use Override;
use Stringable;
use Websymphonie\SharedContext\Domain\Assert;

final readonly class GeneratedMatriculeCode implements Stringable
{
    public function __construct(public string $codeMatricule)
    {
        Assert::notEmpty($this->codeMatricule, message: 'patient_context.exceptions.empty_generated_matricule_code');
    }

    #[Override]
    public function __toString(): string
    {
        return $this->codeMatricule;
    }
}