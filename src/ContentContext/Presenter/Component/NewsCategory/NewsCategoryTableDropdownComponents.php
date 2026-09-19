<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Component\NewsCategory;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\ContentContext\Domain\Model\NewsCategory;
#[AsTwigComponent('NewsCategoryTableDropdown', template: 'content/admin/news_category/components/table_dropdown_component.html.twig')]
final class NewsCategoryTableDropdownComponents { public NewsCategory $category; public string $instanceId; public function mount(NewsCategory $category, string $instanceId = ''): void { $this->category = $category; $this->instanceId = $instanceId !== '' ? $instanceId : 'news-category-' . $category->id; } }
