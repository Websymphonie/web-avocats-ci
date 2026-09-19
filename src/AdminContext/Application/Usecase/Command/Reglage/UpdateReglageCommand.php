<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Application\Usecase\Command\Reglage;

class UpdateReglageCommand
{
    public function __construct(
        public ?int    $id = null,
        public ?string $value = null,
        public ?string $label = null,
        public ?string $type = null,
    )
    {
    }
}