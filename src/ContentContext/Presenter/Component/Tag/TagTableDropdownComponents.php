<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Component\Tag;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\ContentContext\Domain\Model\Tag;
#[AsTwigComponent('TagTableDropdown', template: 'content/admin/tag/components/table_dropdown_component.html.twig')]
final class TagTableDropdownComponents { public Tag $tag; public string $instanceId; public function mount(Tag $tag, string $instanceId = ''): void { $this->tag = $tag; $this->instanceId = $instanceId !== '' ? $instanceId : 'tag-' . $tag->id; } }
