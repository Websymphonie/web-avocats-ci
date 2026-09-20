<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Component\Page;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\ContentContext\Domain\Model\Page;

#[AsTwigComponent('PageTableDropdown', template: 'content/admin/page/components/table_dropdown_component.html.twig')]
final class PageTableDropdownComponent
{
    public Page $page;
    public string $instanceId = '';
}
