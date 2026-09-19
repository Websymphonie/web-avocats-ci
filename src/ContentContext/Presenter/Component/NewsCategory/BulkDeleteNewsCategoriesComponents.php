<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Component\NewsCategory;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
#[AsTwigComponent('BulkDeleteNewsCategories', template: 'content/admin/news_category/components/bulk_delete_component.html.twig')]
final class BulkDeleteNewsCategoriesComponents { public string $formId = 'news-category-bulk-delete'; }
