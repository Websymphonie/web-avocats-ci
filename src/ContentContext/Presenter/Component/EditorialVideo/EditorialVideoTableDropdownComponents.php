<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Component\EditorialVideo;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\ContentContext\Domain\Model\EditorialVideo;
#[AsTwigComponent('EditorialVideoTableDropdown', template: 'content/admin/editorial_video/components/table_dropdown_component.html.twig')]
final class EditorialVideoTableDropdownComponents { public EditorialVideo $video; public string $instanceId; public function mount(EditorialVideo $video, string $instanceId = ''): void { $this->video = $video; $this->instanceId = $instanceId !== '' ? $instanceId : 'video-' . $video->id; } }
