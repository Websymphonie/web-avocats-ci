<?php
declare(strict_types=1);

namespace Websymphonie\WebContext\Presenter\Component\Layout;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\ContentContext\Application\Model\PublishedPage;
use Websymphonie\ContentContext\Application\Usecase\Query\Page\FindPublishedPagesByGroupQuery;
use Websymphonie\ContentContext\Domain\Enum\PageGroup;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryBus;

#[AsTwigComponent('WebNavBar', template: 'web/components/layout/web_navbar_component.html.twig')]
final class WebNavBarComponents
{
    /** @var list<PublishedPage> */
    public array $barPages = [];

    public function __construct(private readonly QueryBus $queryBus)
    {
    }

    public function mount(): void
    {
        /** @var list<PublishedPage> $barPages */
        $barPages = $this->queryBus->handle(new FindPublishedPagesByGroupQuery(PageGroup::BAR));
        $this->barPages = $barPages;
    }
}
