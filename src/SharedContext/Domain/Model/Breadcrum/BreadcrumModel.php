<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Model\Breadcrum;

class BreadcrumModel
{
    public string $name;

    public function __construct(
        public string  $title,
        public ?string $url = null,
    )
    {
        $this->name = $title;
    }
}
