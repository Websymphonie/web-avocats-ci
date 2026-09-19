<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Component\Commons\Breadcrum;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\SharedContext\Domain\Model\Breadcrum\BreadcrumModel;

#[AsTwigComponent('Breadcrumbs', template: 'shared/components/breadcrumb/breadcrumbs_component.html.twig')]
class BreadcrumbsComponents
{
    /** @var list<BreadcrumModel> */
    public array $breadcrumbs = [];
}
