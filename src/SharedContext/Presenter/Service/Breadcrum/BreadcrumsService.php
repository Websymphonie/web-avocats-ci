<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Service\Breadcrum;

use Websymphonie\SharedContext\Domain\Model\Breadcrum\BreadcrumModel;
use Websymphonie\SharedContext\Domain\Service\Helper\BreadcrumsServiceInterface;

class BreadcrumsService implements BreadcrumsServiceInterface
{
    /** @var list<BreadcrumModel> */
    private array $breadcrumbs = [];

    public function addBreadcrumb(string $title, string $url): self
    {
        $this->breadcrumbs[] = new BreadcrumModel(title: $title, url: $url);

        return $this;
    }

    /** @return list<BreadcrumModel> */
    public function getBreadcrumbs(): array
    {
        return $this->breadcrumbs;
    }
}
