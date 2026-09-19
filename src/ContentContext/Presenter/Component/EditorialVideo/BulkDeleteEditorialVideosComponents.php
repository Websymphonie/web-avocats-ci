<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Component\EditorialVideo;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
#[AsTwigComponent('BulkDeleteEditorialVideos', template: 'content/admin/editorial_video/components/bulk_delete_component.html.twig')]
final class BulkDeleteEditorialVideosComponents { public string $formId = 'editorial-video-bulk-delete'; }
