<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Model;

final readonly class EnrollmentUser
{
    public function __construct(public int $id, public string $name, public string $email, public bool $enabled) {}
}
