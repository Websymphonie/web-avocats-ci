<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Component\Tag;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
#[AsTwigComponent('BulkDeleteTags', template: 'content/admin/tag/components/bulk_delete_component.html.twig')]
final class BulkDeleteTagsComponents { public string $formId = 'tag-bulk-delete'; }
