<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Component\EditorialVideoCategory;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
#[AsTwigComponent('BulkDeleteEditorialVideoCategories', template: 'content/admin/editorial_video_category/components/bulk_delete_component.html.twig')]
final class BulkDeleteEditorialVideoCategoriesComponents { public string $formId = 'editorial-video-category-bulk-delete'; }
