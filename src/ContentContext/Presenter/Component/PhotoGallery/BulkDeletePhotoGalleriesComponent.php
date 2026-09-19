<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Component\PhotoGallery;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('BulkDeletePhotoGalleries', template: 'content/admin/photo_gallery/components/bulk_delete_component.html.twig')]
final class BulkDeletePhotoGalleriesComponent { public string $formId = 'photo-gallery-bulk-delete'; }
