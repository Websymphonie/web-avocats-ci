<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Component\PhotoGallery;

use Websymphonie\ContentContext\Domain\Model\PhotoGallery;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('PhotoGalleryTableDropdown', template: 'content/admin/photo_gallery/components/table_dropdown_component.html.twig')]
final class PhotoGalleryTableDropdownComponent { public PhotoGallery $gallery; public string $instanceId = ''; }
