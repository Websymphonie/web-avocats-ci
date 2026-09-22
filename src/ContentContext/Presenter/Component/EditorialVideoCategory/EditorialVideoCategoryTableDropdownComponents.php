<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Component\EditorialVideoCategory;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\ContentContext\Domain\Model\EditorialVideoCategory;
#[AsTwigComponent('EditorialVideoCategoryTableDropdown', template: 'content/admin/editorial_video_category/components/table_dropdown_component.html.twig')]
final class EditorialVideoCategoryTableDropdownComponents { public EditorialVideoCategory $category; public string $instanceId; public function mount(EditorialVideoCategory $category, string $instanceId = ''): void { $this->category = $category; $this->instanceId = $instanceId !== '' ? $instanceId : 'editorial-video-category-' . $category->id; } }
