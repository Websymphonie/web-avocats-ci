<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\ValueObject;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class Address
{
    public function __construct(
        #[ORM\Column(type: "string", length: 255, nullable: true)]
        public ?string $address = null
    )
    {

    }

    public function __toString(): string
    {
        return $this->address;
    }
}