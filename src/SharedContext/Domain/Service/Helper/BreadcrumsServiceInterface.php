<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Service\Helper;

use Websymphonie\SharedContext\Domain\Model\Breadcrum\BreadcrumModel;

interface BreadcrumsServiceInterface
{
    public function addBreadcrumb(string $title, string $url): self;

    /** @return list<BreadcrumModel> */
    public function getBreadcrumbs(): array;
}
