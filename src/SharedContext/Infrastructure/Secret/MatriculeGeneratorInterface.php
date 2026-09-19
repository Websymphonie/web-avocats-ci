<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Infrastructure\Secret;


use Websymphonie\SharedContext\Domain\Model\ValueObject\GeneratedMatriculeCode;

interface MatriculeGeneratorInterface
{
    public function generate(string $firstName, string $lastName): GeneratedMatriculeCode;
}