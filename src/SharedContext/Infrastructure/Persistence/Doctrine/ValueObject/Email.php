<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\ValueObject;

use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Webmozart\Assert\Assert;

#[ORM\Embeddable]
final readonly class Email implements Stringable
{
    #[ORM\Column(type: "string", length: 180, unique: true)]
    public string $value;

    public function __construct(string $value)
    {
        Assert::notEmpty($value);
        Assert::email($value);

        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}