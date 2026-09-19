<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Component\News;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\ContentContext\Domain\Model\News;

#[AsTwigComponent('NewsTableDropdown', template: 'content/admin/news/components/news_table_dropdown_component.html.twig')]
final class NewsTableDropdownComponents
{
    public News $news;
    public string $instanceId;

    public function mount(News $news, string $instanceId = ''): void
    {
        $this->news = $news;
        $this->instanceId = $instanceId !== '' ? $instanceId : 'news-' . $news->id;
    }
}
